<?php

namespace App\Controller;

use App\Entity\Bet;
use App\Repository\BetRepository;
use App\Repository\GameRepository;
use App\Service\ConfigService;
use App\Service\DataFormatService;
use Doctrine\Common\Collections\Collection;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class IndexController extends AbstractController
{
    private const CACHE_TTL = 3600;

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/', name: 'app.index')]
    public function index(
        Request $request,
        GameRepository $gameRepository,
        BetRepository $betRepository,
        ConfigService $configService,
        CacheInterface $cache,
        DataFormatService $dataFormatService
    ): Response {
        $currentMatchday = (int) $configService->get('currentMatchday');
        $lastMatchday = $currentMatchday - 1;

        $currentMatchdayGames = $this->getGamesForMatchdayCached($cache, $gameRepository, $currentMatchday);

        $currentPlayerBet = [];
        $user = $this->getUser();

        if ($user !== null) {
            $userId = (int) $user->getId();
            $currentPlayerBet = $this->getCurrentPlayerBetCached($cache, $betRepository, $userId, $currentMatchday);
            $currentPlayerBet = $this->mapBets($currentPlayerBet);
        }

        $lastMatchdayGames = [];
        if ($lastMatchday > 0) {
            $lastMatchdayGames = $this->getGamesForMatchdayCached($cache, $gameRepository, $lastMatchday);
        }

        return $this->render('index/index.html.twig', [
            'currentMatchday' => $currentMatchday,
            'lastMatchday' => $lastMatchday,
            'currentMatchdayGames' => $currentMatchdayGames,
            'currentPlayerBet' => $currentPlayerBet,
            'lastMatchdayGames' => $lastMatchday > 0 ? $lastMatchdayGames : [],
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getGamesForMatchdayCached(
        CacheInterface $cache,
        GameRepository $gameRepository,
        int $matchday
    ): array {
        $cacheKey = $this->cacheKeyGames($matchday);

        return $cache->get($cacheKey, function (ItemInterface $item) use ($gameRepository, $matchday) {
            $item->expiresAfter(self::CACHE_TTL);
            return $gameRepository->findArrayByMatchday($matchday);
        });
    }

    private function getCurrentPlayerBetCached(
        CacheInterface $cache,
        BetRepository $betRepository,
        int $userId,
        int $currentMatchday
    ): array {
        $cacheKey = $this->cacheKeyCurrentPlayerBet($userId);

        return $cache->get($cacheKey, function (ItemInterface $item) use ($betRepository, $currentMatchday, $userId) {
            $item->expiresAfter(self::CACHE_TTL);
            return $betRepository->findArrayCurrentPlayerBet($currentMatchday, $userId);
        });
    }

    private function mapBets(array $bets): array
    {
        $out = [];
        foreach ($bets as $b) {
            $game = $b->getGameId();
            $out[] = [
                'gameId' => $game?->getId(),
                'homeGoals' => $b->getHomeGoals(),
                'awayGoals' => $b->getAwayGoals(),
            ];
        }
        return $out;
    }

    private function cacheKeyGames(int $matchday): string
    {
        return sprintf('games.view.matchday.%d', $matchday);
    }

    private function cacheKeyCurrentPlayerBet(int $userId): string
    {
        return sprintf('player.currentBet.%d', $userId);
    }
}
