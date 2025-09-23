<?php

namespace App\Service;

use App\Repository\SystemConfigRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

readonly class ConfigService
{
    public function __construct(private SystemConfigRepository $repo,
                                private CachingService $cache
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key): ?string
    {
        return $this->cache->getConfig($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function set(string $key, ?string $value): void
    {
        $this->repo->set($key, $value);
        $this->cache->deleteConfig($key);
    }
}
