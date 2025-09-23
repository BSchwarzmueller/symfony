<?php

namespace App\Service;

use App\Repository\BetRepository;
use App\Repository\GameRepository;
use App\Repository\SystemConfigRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CachingService
{
    const CACHE_TTL = 5 * 60;
    const BETS_CACHE = 'bets';
    const OPEN_GAMES_CACHE = 'openGames';
    const CURRENT_MATCHDAY_CACHE = 'currentMatchday';
    const CACHE_SEPARATOR = '.';
    const CONFIG_CACHE = 'config';
    const CURRENT_MATCHDAY_CONFIG_KEY = 'currentMatchday';

    public function __construct(
        private readonly CacheInterface         $cache,
        private readonly BetRepository          $betRepository,
        private readonly GameRepository         $gameRepository,
        private readonly SystemConfigRepository $systemConfigRepository,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteUserCache(string $type, string $userId): void
    {
        $prefix = match ($type) {
            'bets' => self::BETS_CACHE,
            'openGames' => self::OPEN_GAMES_CACHE,
        };

        $this->cache->delete($prefix . self::CACHE_SEPARATOR . $userId);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getCurrentMatchdayGames(int $currentMatchday):array
    {
        return $this->cache->get(self::CURRENT_MATCHDAY_CACHE,
            function (ItemInterface $item) use ($currentMatchday){
                $item->expiresAfter(60*60*24);

                return $this->gameRepository->findArrayByMatchday($currentMatchday);
            });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getUserOpenGames(int $userId, array $matches, array $openBetGameIds)
    {
        return $this->cache->get(self::OPEN_GAMES_CACHE . self::CACHE_SEPARATOR . $userId,
            function (ItemInterface $item) use ($matches, $openBetGameIds) {
                $item->expiresAfter(self::CACHE_TTL);
                return array_filter($matches, function ($game) use ($openBetGameIds) {
                    return !isset($openBetGameIds[$game->getId()]);
                });
            });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getUserBets(int $userId)
    {
        return $this->cache->get(self::BETS_CACHE . self::CACHE_SEPARATOR . $userId,
            function (ItemInterface $item) use ($userId) {
                $item->expiresAfter(self::CACHE_TTL);
                return $this->betRepository->getBetArrayByUser($userId);
            });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getConfig(string $key)
    {
        return $this->cache->get(self::CONFIG_CACHE . self::CACHE_SEPARATOR . $key,
            function () use ($key) {
                $config = $this->systemConfigRepository->get($key);
                return $config?->getConfigValue();
            });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteConfig(string $key): void
    {
        $this->cache->delete(self::CONFIG_CACHE . self::CACHE_SEPARATOR . $key);
    }
}