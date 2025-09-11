<?php

namespace App\Service;

use App\Entity\Game;
use http\Exception\InvalidArgumentException;

readonly class DataFormatService
{
    public function __construct(ConfigService $configService)
    {
    }

    public function createMatchData(array $games): array
    {
        return array_map(static function ($g) {
            if(!$g instanceof Game) {
                throw new InvalidArgumentException('Invalid game type: '.get_class($g));
            }

            $home = $g->getHomeClub();
            $away = $g->getAwayClub();

            return [
                'id' => $g->getId(),
                'day' => $g->getDate()?->format('d.M'),
                'time' => $g->getDate()?->format('H:i'),
                'homeGoals' => $g->getHomeGoals() ?? '-',
                'awayGoals' => $g->getAwayGoals() ?? '-',
                'homeClub' => [
                    'name' => $home->getName(),
                ],
                'awayClub' => [
                    'name' => $away->getName(),
                ],
            ];
        }, $games);
    }
}
