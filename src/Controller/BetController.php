<?php

namespace App\Controller;

use App\Entity\Bet;
use App\Entity\User;
use App\Event\BetPlacedEvent;
use App\Repository\BetRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\BetProcessingService;
use App\Service\CachingService;
use App\Service\ConfigService;
use App\Service\DataFormatService;
use App\Service\FactoryService;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BetController extends AbstractController
{

    const OPEN_BET_STATUS = 'open';
    const CLOSED_BET_STATUS = 'closed';

    public function __construct(private readonly BetRepository            $betRepository,
                                private readonly ConfigService            $configService,
                                private readonly DataFormatService        $format,
                                private readonly CachingService           $cache,
                                private readonly LoggerInterface          $logger,
                                private readonly EventDispatcherInterface $dispatcher,
                                private readonly ValidatorInterface       $validator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[
        Route(path: 'bets/{id}', name: 'app.bets.show')]
    public function showBetView(User $user): Response
    {
        try {
            $userId = $user->getId();
            $bets   = $this->cache->getUserBets($userId);
            $games  = $this->splitGames($bets, $userId);

            return $this->render('bets/index.html.twig', [
                'userId' => $userId,
                'games'  => $games,
            ]);
        } catch (Exception $e) {
            $this->logger->error('Error fetching bets', ['error' => $e->getMessage()]);
            throw new RuntimeException($e->getMessage(), previous: $e);
        }

    }

    #[Route(path: '/bet/create', name: 'app.bet.create', methods: ['POST'])]
    final public function createBet(
        Request        $request,
        BetRepository  $betRepository,
        FactoryService $factoryService,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $data = json_decode($request->getContent(), true);

            $createBetDto = $factoryService->createBetDtoFromRequestData($data);

            if (!$createBetDto) {
                return $this->json(['error' => 'Missing request data'], 400);
            }

            $errors = $this->validator->validate($createBetDto);

            if (count($errors) > 0) {
                return $this->json(['error' => 'Invalid request data' . $errors], 400);
            }

            $bet = $factoryService->createBet($createBetDto);

            if (!$betRepository->store($bet)) {
                return $this->json(['error' => 'Failed to create bet'], 500);
            }

            $this->deleteUserCaches($createBetDto->getUserId());

            $event = $factoryService->createBetPlacedEvent($createBetDto);
            $this->dispatcher->dispatch($event, BetPlacedEvent::NAME);

            return $this->json(['message' => 'Bet created successfully'], 201);
        } catch (Exception|InvalidArgumentException $e) {
            $this->logger->error('Error creating bet', ['error' => $e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }


    #[Route(path: 'bet/process/{id}', name: 'app.bets.process', methods: ['GET'])]
    public function processBet(Bet $bet, Request $request, BetProcessingService $betProcessingService)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($bet->getStatus() === self::CLOSED_BET_STATUS) {
            return $this->json(['error' => 'Bet is already processed'], 400);
        }

        try {
            $user = $this->getUser();

            if(!$user && $user->getId() !== $bet->getUser()->getId()) {
                return $this->json(['error' => 'You are not allowed to process this bet'], 403);
            }

            $points = $betProcessingService->processBet($bet);

            if($points < 0) {
                return $this->json(['error' => 'Failed to process bet'], 500);
            }

            $bet->setPoints($points);
            $bet->setStatus(self::CLOSED_BET_STATUS);
            $this->betRepository->store($bet);

            $this->deleteUserCaches($bet->getUser()->getId());

            return $this->json([
                'message' => 'Bet processed successfully',
                'points' => $points,
            ], 200);
        } catch (Exception $e) {
            $this->logger->error('Error processing bets', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function deleteUserCaches(int $userId): void
    {
        $this->cache->deleteUserCache('openGames', $userId);
        $this->cache->deleteUserCache('bets', $userId);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getCurrentMatchDay(): array
    {
        try {
            return $this->cache->getCurrentMatchdayGames($this->configService->get('currentMatchday'));
        } catch (Exception $e) {
            $this->logger->error('Error fetching current matchday', ['error' => $e->getMessage()]);
            throw new RuntimeException($e->getMessage(), previous: $e);
        }
    }

    private function getClosedBets(array $bets): array
    {
        return array_filter($bets, function ($bet) {
            return $bet['status'] === self::CLOSED_BET_STATUS;
        });
    }

    private function getOpenBets(array $bets): array
    {
        return array_filter($bets, function ($bet) {
            return $bet['status'] === self::OPEN_BET_STATUS;
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getOpenGames(array $matches, array $openBets, int $userId): array
    {
        $openBetGameIds = [];
        foreach ($openBets as $bet) {
            if (isset($bet['gameId']['id'])) {
                $openBetGameIds[(int)$bet['gameId']['id']] = true;
            }
        }

        return $this->cache->getUserOpenGames($userId, $matches, $openBetGameIds);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function splitGames(array $bets, int $userId): array
    {
        $closedBets = $this->getClosedBets($bets);
        $openBets   = $this->getOpenBets($bets);
        $openGames  = $this->getOpenGames($this->getCurrentMatchDay(), $openBets, $userId);;

        return $this->format->GamesForBetView($openGames, $closedBets, $openBets, $userId);
    }
}
