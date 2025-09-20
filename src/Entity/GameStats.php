<?php

namespace App\Entity;

use App\Repository\GameStatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameStatsRepository::class)]
class GameStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\Column]
    private ?float $avgHomeGoals = null;

    #[ORM\Column]
    private ?float $avgAwayGoals = null;

    #[ORM\Column]
    private ?int $numberOfVotes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function getAvgHomeGoals(): ?float
    {
        return $this->avgHomeGoals;
    }

    public function setAvgHomeGoals(float $avgHomeGoals): static
    {
        $this->avgHomeGoals = $avgHomeGoals;

        return $this;
    }

    public function getAvgAwayGoals(): ?float
    {
        return $this->avgAwayGoals;
    }

    public function setAvgAwayGoals(float $avgAwayGoals): static
    {
        $this->avgAwayGoals = $avgAwayGoals;

        return $this;
    }

    public function getNumberOfVotes(): ?int
    {
        return $this->numberOfVotes;
    }

    public function setNumberOfVotes(int $numberOfVotes): static
    {
        $this->numberOfVotes = $numberOfVotes;

        return $this;
    }
}
