<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateBetDto
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThan(0)]
    private ?int $userId;
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThan(0)]
    private ?int $gameId;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $homeGoals;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $awayGoals;

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getGameId(): ?int
    {
        return $this->gameId;
    }

    public function setGameId(?int $gameId): void
    {
        $this->gameId = $gameId;
    }

    public function getHomeGoals(): ?int
    {
        return $this->homeGoals;
    }

    public function setHomeGoals(?int $homeGoals): void
    {
        $this->homeGoals = $homeGoals;
    }

    public function getAwayGoals(): ?int
    {
        return $this->awayGoals;
    }

    public function setAwayGoals(?int $awayGoals): void
    {
        $this->awayGoals = $awayGoals;
    }

}