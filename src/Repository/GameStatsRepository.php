<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\GameStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameStats>
 */
class GameStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameStats::class);
    }

    /**
     * @throws ORMException
     */
    public function store(int $gameId, int $homeGoals, int $awayGoals, int $numberOfVotes = 1): void
   {
       $em = $this->getEntityManager();
       $game = $em->getReference(Game::class, $gameId);

       $gameStats = new GameStats();
       $gameStats->setGame($game);
       $gameStats->setAvgHomeGoals($homeGoals);
       $gameStats->setAvgAwayGoals($awayGoals);
       $gameStats->setNumberOfVotes($numberOfVotes);

       $em->persist($gameStats);
       $em->flush();
   }

    /**
     * @throws ORMException
     */
    public function update(int $gameId, int $homeGoals, int $awayGoals): void
   {
       $em = $this->getEntityManager();
       $game = $em->getReference(Game::class, $gameId);
       $gameStats = $em->getRepository(GameStats::class)->findOneBy(['game' => $game]);

       $numberOfVotes = $gameStats->getNumberOfVotes() + 1;
       $avgHomeGoals = ($gameStats->getAvgHomeGoals() * $gameStats->getNumberOfVotes() + $homeGoals) / $numberOfVotes;
       $avgAwayGoals = ($gameStats->getAvgAwayGoals() * $gameStats->getNumberOfVotes() + $awayGoals) / $numberOfVotes;

       $gameStats->setAvgHomeGoals($avgHomeGoals);
       $gameStats->setAvgAwayGoals($avgAwayGoals);
       $gameStats->setNumberOfVotes($numberOfVotes);

       $em->persist($gameStats);
       $em->flush();
   }
}
