<?php

namespace App\Event;

use App\Entity\Game;
use App\Repository\GameStatsRepository;
use Symfony\Contracts\EventDispatcher\Event;

class BetPlacedEvent extends Event
{
    public const NAME = 'bet.placed';

    public function __construct(
        private int $gameId,
        private int $homeGoals,
        private int $awayGoals
    ) {}
    public function getGameId(): int
    {
        return $this->gameId;
    }
    public function getHomeGoals(): int
    {
        return $this->homeGoals;
    }
    public function getAwayGoals(): int
    {
        return $this->awayGoals;
    }
}
