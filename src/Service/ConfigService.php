<?php

namespace App\Service;

use App\Repository\SystemConfigRepository;

class ConfigService
{
    public function __construct(private readonly SystemConfigRepository $repo) {}

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->repo->get($key, $default);
    }

    public function set(string $key, ?string $value): void
    {
        $this->repo->set($key, $value);
    }
}
