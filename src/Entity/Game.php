<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Club $homeClub = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Club $awayClub = null;

    #[ORM\Column(nullable: true)]
    private ?int $homeGoals = null;

    #[ORM\Column(nullable: true)]
    private ?int $awayGoals = null;

    #[ORM\Column(length: 255)]
    private ?string $competition = null;

    #[ORM\Column(length: 255)]
    private ?string $season = null;

    #[ORM\Column]
    private ?int $matchday = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    private ?bool $processed = null;

    #[ORM\Column(nullable: true)]
    private ?int $openLigaId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHomeClub(): ?Club
    {
        return $this->homeClub;
    }

    public function setHomeClub(?Club $homeClub): static
    {
        $this->homeClub = $homeClub;

        return $this;
    }

    public function getAwayClub(): ?Club
    {
        return $this->awayClub;
    }

    public function setAwayClub(?Club $awayClub): static
    {
        $this->awayClub = $awayClub;

        return $this;
    }

    public function getHomeGoals(): ?int
    {
        return $this->homeGoals;
    }

    public function setHomeGoals(?int $homeGoals): static
    {
        $this->homeGoals = $homeGoals;

        return $this;
    }

    public function getAwayGoals(): ?int
    {
        return $this->awayGoals;
    }

    public function setAwayGoals(?int $awayGoals): static
    {
        $this->awayGoals = $awayGoals;

        return $this;
    }

    public function getCompetition(): ?string
    {
        return $this->competition;
    }

    public function setCompetition(string $competition): static
    {
        $this->competition = $competition;

        return $this;
    }

    public function getSeason(): ?string
    {
        return $this->season;
    }

    public function setSeason(string $season): static
    {
        $this->season = $season;

        return $this;
    }

    public function getMatchday(): ?int
    {
        return $this->matchday;
    }

    public function setMatchday(int $matchday): static
    {
        $this->matchday = $matchday;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function isProcessed(): ?bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): static
    {
        $this->processed = $processed;

        return $this;
    }

    public function getOpenLigaId(): ?int
    {
        return $this->openLigaId;
    }

    public function setOpenLigaId(?int $openLigaId): static
    {
        $this->openLigaId = $openLigaId;

        return $this;
    }

    public function getAvgHomeGoals(): ?GameStats
    {
        return $this->avgHomeGoals;
    }

    public function setAvgHomeGoals(GameStats $avgHomeGoals): static
    {
        // set the owning side of the relation if necessary
        if ($avgHomeGoals->getGame() !== $this) {
            $avgHomeGoals->setGame($this);
        }

        $this->avgHomeGoals = $avgHomeGoals;

        return $this;
    }

    public function getNo(): ?GameStats
    {
        return $this->no;
    }

    public function setNo(GameStats $no): static
    {
        // set the owning side of the relation if necessary
        if ($no->getGame() !== $this) {
            $no->setGame($this);
        }

        $this->no = $no;

        return $this;
    }
}
