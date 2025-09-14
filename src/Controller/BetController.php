<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BetRepository;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\ConfigService;
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
    const OPEN_BET_STATUS = 'open';
    const CLOSED_BET_STATUS = 'closed';
    const CURRENT_MATCHDAY_CACHE_KEY = 'currentMatchday';
    const CURRENT_MATCHDAY_CONFIG_KEY = 'currentMatchday';

    public function __construct(private readonly BetRepository   $betRepository,
                                private readonly GameRepository  $gameRepository,
                                private readonly ConfigService   $configService,
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
            $closedBets = $this->getClosedBets($bets);
            $openBets = $this->getOpenBets($bets);
            $openGames = $this->getOpenGames($this->getOrCreateCurrentMatchDayCache(), $openBets);;

            $games = $this->prepareGameDataForVue($openGames, $closedBets, $openBets, $user);

            return $this->render('bets/index.html.twig', [
                'userId' => $user->getId(),
                'games' => $games,
            ]);
        } catch (Exception $e) {
            $this->logger->error('Error fetching bets', ['error' => $e->getMessage()]);
            throw new RuntimeException($e->getMessage(), previous: $e);
        }

    }

    #[Route(path: '/bet/create', name: 'app.bet.create', methods: ['POST'])]
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
                self::OPEN_BET_STATUS
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
        return $this->cache->get(self::BETS_CACHE_KEY . '.' . $userId,
            function (ItemInterface $item) use ($userId) {
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

    /**
     * @throws InvalidArgumentException
     */
    private function getOrCreateCurrentMatchDayCache(): array
    {
        try {
            $currentMatchday = $this->configService->get(self::CURRENT_MATCHDAY_CONFIG_KEY);
            return $this->cache->get(self::CURRENT_MATCHDAY_CACHE_KEY . '.' . $currentMatchday,
                function (ItemInterface $item) use ($currentMatchday) {
                    $item->expiresAfter(self::CACHE_TTL);
                    return $this->gameRepository->findByMatchday($currentMatchday);
                });
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

    private function getOpenGames(array $getOrCreateCurrentMatchDayCache, array $openBets): array
    {
        return array_filter($getOrCreateCurrentMatchDayCache, function ($game) use ($openBets) {
            return !isset($openBets[$game->getId()]);
        });
    }

    private function prepareGameDataForVue(array $openGames, array $closedBets, array $openBets, User $user): array
    {
        $out = [];
        foreach ($openGames as $game) {
            $out[] = [
                'type' => 'openGame',
                'gameId' => $game->getId(),
                'userId' => $user->getId(),
                'homeClub' => $game->getHomeClub()->getName(),
                'awayClub' => $game->getAwayClub()->getName(),
                'homeGoals' => $game->getHomeGoals(),
                'awayGoals' => $game->getAwayGoals(),
                'competition' => $game->getCompetition(),
                'season' => $game->getSeason(),
                'matchday' => $game->getMatchday(),
                'date' => $game->getDate()->format('Y-m-d'),
                'betHomeGoals' => -1,
                'betAwayGoals' => -1,
                'betStatus' => null,
                'betPoints' => null,
            ];
        }
        foreach ($closedBets as $bet) {
            $out[] = [
                'type' => 'closedBet',
                'id' => $bet['gameId']['id'],
                'userId' => $user->getId(),
                'homeClub' => $bet['gameId']['homeClub']['name'],
                'awayClub' => $bet['gameId']['awayClub']['name'],
                'homeGoals' => $bet['gameId']['homeGoals'],
                'awayGoals' => $bet['gameId']['awayGoals'],
                'competition' => $bet['gameId']['competition'],
                'season' => $bet['gameId']['season'],
                'matchday' => $bet['gameId']['matchday'],
                'date' => $bet['gameId']['date'],
                'betHomeGoals' => $bet['homeGoals'],
                'betAwayGoals' => $bet['awayGoals'],
                'betStatus' => $bet['status'],
                'betPoints' => $bet['points'],
            ];
        }
        foreach ($openBets as $bet) {
            $out[] = [
                'type' => 'openBet',
                'id' => $bet['gameId']['id'],
                'userId' => $user->getId(),
                'homeClub' => $bet['gameId']['homeClub']['name'],
                'awayClub' => $bet['gameId']['awayClub']['name'],
                'homeGoals' => $bet['gameId']['homeGoals'],
                'awayGoals' => $bet['gameId']['awayGoals'],
                'competition' => $bet['gameId']['competition'],
                'season' => $bet['gameId']['season'],
                'matchday' => $bet['gameId']['matchday'],
                'date' => $bet['gameId']['date'],
                'betHomeGoals' => $bet['homeGoals'],
                'betAwayGoals' => $bet['awayGoals'],
                'betStatus' => $bet['status'],
                'betPoints' => $bet['points'],
            ];
        }
        return $out;
    }
}
