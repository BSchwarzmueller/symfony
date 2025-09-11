<?php

namespace App\Repository;

use App\Entity\Club;
use App\Entity\Game;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findByMatchday(int $matchday, ?string $sort = 'ASC'): array
    {
        return $this->createQueryBuilder('g')
            // Passe die Property-Namen an dein Entity an:
            ->leftJoin('g.homeClub', 'hc')->addSelect('hc')
            ->leftJoin('g.awayClub', 'ac')->addSelect('ac')
            ->where('g.matchday = :md')
            ->setParameter('md', $matchday)
            ->orderBy('g.date', $sort)
            ->getQuery()
            ->getResult();

    }

    /**
     * @throws \Exception
     */
    public function create(
        int               $openLigaId,
        int               $homeId,
        int               $awayId,
        int|null          $homeGoals,
        int|null          $awayGoals,
        DateTimeImmutable $date,
        int               $matchday,
        ?string           $competition = 'bl1',
        ?string           $season = '2025',
    ): void
    {
        if ($this->findOneBy(['openLigaId' => $openLigaId]) !== null) {
            return;
        }
        $em = $this->getEntityManager();
        /** @var Club|null $homeClub */
        $homeClub = $em->getRepository(Club::class)->findOneBy(['openLigaId' => $homeId]);
        /** @var Club|null $awayClub */
        $awayClub = $em->getRepository(Club::class)->findOneBy(['openLigaId' => $awayId]);

        $game = new Game();

        $game->setOpenLigaId($openLigaId);
        $game->setHomeClub($homeClub);
        $game->setAwayClub($awayClub);
        $game->setMatchday($matchday);
        $game->setHomeGoals($homeGoals);
        $game->setAwayGoals($awayGoals);
        $game->setCompetition($competition);
        $game->setSeason($season);
        $game->setDate($date);
        $game->setProcessed(false);

        $this->getEntityManager()->persist($game);
        $this->getEntityManager()->flush();
    }

    public function getFutureGames(): array
    {
        return $this->findBy([
            'homeGoals' => null,
            'awayGoals' => null,
        ]);
    }

    public function getPlayedGames(): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.homeGoals IS NOT NULL AND g.awayGoals IS NOT NULL')
            ->getQuery()->getResult();
    }
}
