<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class GameDto
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThan(0)]
    private ?int $openLigaId = null;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThan(0)]
    private ?int $homeId = null;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThan(0)]
    private ?int $awayId = null;

    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Assert\GreaterThanOrEqual(-1)]
    private ?int $homeScore = null;

    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Assert\GreaterThanOrEqual(-1)]
    private ?int $awayScore = null;

    #[Assert\NotBlank]
    #[Assert\DateTime]
    private ?string $date = null;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThan(0)]
    private ?int $matchday = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    private ?string $competition = null;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThan(1900)]
    private ?int $season = null;

    public function getOpenLigaId(): ?int
    {
        return $this->openLigaId;
    }

    public function setOpenLigaId(?int $openLigaId): void
    {
        $this->openLigaId = $openLigaId;
    }

    public function getHomeId(): ?int
    {
        return $this->homeId;
    }

    public function setHomeId(?int $homeId): void
    {
        $this->homeId = $homeId;
    }

    public function getAwayId(): ?int
    {
        return $this->awayId;
    }

    public function setAwayId(?int $awayId): void
    {
        $this->awayId = $awayId;
    }

    public function getHomeScore(): ?int
    {
        return $this->homeScore;
    }

    public function setHomeScore(?int $homeScore): void
    {
        $this->homeScore = $homeScore;
    }

    public function getAwayScore(): ?int
    {
        return $this->awayScore;
    }

    public function setAwayScore(?int $awayScore): void
    {
        $this->awayScore = $awayScore;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): void
    {
        $this->date = $date;
    }

    public function getMatchday(): ?int
    {
        return $this->matchday;
    }

    public function setMatchday(?int $matchday): void
    {
        $this->matchday = $matchday;
    }

    public function getCompetition(): ?string
    {
        return $this->competition;
    }

    public function setCompetition(?string $competition): void
    {
        $this->competition = $competition;
    }

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(?int $season): void
    {
        $this->season = $season;
    }
}
