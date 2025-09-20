<?php

namespace App\Service;

use App\Repository\SystemConfigRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

readonly class ConfigService
{
    public function __construct(private SystemConfigRepository $repo, private CacheInterface $cache) {}

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return $this->cache->get('config_'.$key, function () use ($key, $default) {
            $config = $this->repo->get($key);
            return $config?->getConfigValue() ?? $default;
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function set(string $key, ?string $value): void
    {
        $this->repo->set($key, $value);
        $this->cache->delete('config_'.$key);
    }
}
