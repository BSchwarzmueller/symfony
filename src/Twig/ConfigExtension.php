<?php

namespace App\Twig;

use App\Service\ConfigService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class ConfigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private readonly ConfigService $configService) {}

    public function getGlobals(): array
    {
        return [
            'config' => $this->configService,
        ];
    }
}
