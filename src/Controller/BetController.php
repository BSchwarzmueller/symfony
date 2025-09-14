<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BetRepository;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BetController extends AbstractController
{
    const CACHE_TTL = 5 * 60;
    const BETS_CACHE_KEY = 'bets';
    const NEW_BET_STATUS = 'open';

    public function __construct(private readonly BetRepository   $betRepository,
                                private readonly CacheInterface  $cache,
                                private readonly LoggerInterface $logger
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route(path: 'bets/{id}', name: 'app.bets.show')]
    public function showBetView(User $user): Response
    {
        try {
            $bets = $this->getOrCreateBetCache($user->getId());

            return $this->render('bets/index.html.twig', [
                'user' => $user,
                'bets' => $bets,
            ]);
        } catch (Exception $e) {
            $this->logger->error('Error fetching bets', ['error' => $e->getMessage()]);
            throw new RuntimeException($e->getMessage(), previous: $e);
        }

    }

    #[Route(path: '/bet/create', name: 'api.bet.create', methods: ['POST'])]
    final public function createBet(
        Request       $request,
        BetRepository $betRepository,
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['userId'], $data['gameId'], $data['homeGoals'], $data['awayGoals'])) {
                return $this->json(['error' => 'Missing required fields'], 400);
            }

            if (!$betRepository->store(
                (int)$data['userId'],
                (int)$data['gameId'],
                (int)$data['homeGoals'],
                (int)$data['awayGoals'],
                self::NEW_BET_STATUS
            )) {
                return $this->json(['error' => 'Failed to create bet'], 500);
            }

            $this->deleteBetCache((int)$data['userId']);

            return $this->json(['message' => 'Bet created successfully'], 201);
        } catch (Exception|InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }


    /**
     * @throws InvalidArgumentException
     */
    private function getOrCreateBetCache(int $userId): array
    {
        return $this->cache->get(self::BETS_CACHE_KEY . '.' . $userId, function (ItemInterface $item) use ($userId) {
            $item->expiresAfter(self::CACHE_TTL);
            return $this->betRepository->getBetArrayByUser($userId);
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    private function deleteBetCache(int $userId): void
    {
        $this->cache->delete(self::BETS_CACHE_KEY . '.' . $userId);
    }

}
