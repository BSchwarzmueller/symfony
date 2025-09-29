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
    const THIRTY_MINUTES = 30 * 60;
    const A_DAY = 24 * 60 * 60;
    const FIVE_MINUTES = 5 * 60;
    const BETS_CACHE = 'bets';
    const OPEN_GAMES_CACHE = 'openGames';
    const CURRENT_MATCHDAY_CACHE = 'currentMatchday';
    const CACHE_SEPARATOR = '.';
    const CONFIG_CACHE = 'config';

    public function __construct(
        private readonly CacheInterface         $cache,
        private readonly BetRepository          $betRepository,
        private readonly GameRepository         $gameRepository,
        private readonly SystemConfigRepository $systemConfigRepository,
    ) {
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
    public function getCurrentMatchdayGames(int $currentMatchday): array
    {
        return $this->cache->get(self::CURRENT_MATCHDAY_CACHE,
            function (ItemInterface $item) use ($currentMatchday) {
                $item->expiresAfter(self::FIVE_MINUTES);
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
                $item->expiresAfter(self::THIRTY_MINUTES);
                return array_filter($matches, function ($game) use ($openBetGameIds) {
                    return !isset($openBetGameIds[$game['id']]);
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
                $item->expiresAfter(self::THIRTY_MINUTES);
                return $this->betRepository->getBetArrayByUser($userId);
            });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getConfig(string $key): ?string
    {
        return $this->cache->get(self::CONFIG_CACHE . self::CACHE_SEPARATOR . $key,
            function (ItemInterface $item) use ($key) {
                $item->expiresAfter(self::A_DAY);
                return $this->systemConfigRepository->get($key)?->getConfigValue();
            });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteConfig(string $key): void
    {
        $this->cache->delete(self::CONFIG_CACHE . self::CACHE_SEPARATOR . $key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteCurrentMatchdayCache():void
    {
        $this->cache->delete(self::CURRENT_MATCHDAY_CACHE);
    }
}