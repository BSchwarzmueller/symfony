<?php

namespace App\Repository;

use App\Dto\GameDto;
use App\Entity\Club;
use App\Entity\Game;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validation;

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

    public function findArrayByMatchday(int $matchday, ?string $sort = 'ASC'): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.homeClub', 'hc')->addSelect('hc')
            ->leftJoin('g.awayClub', 'ac')->addSelect('ac')
            ->where('g.matchday = :md')
            ->andWhere('g.date > :now')
            ->setParameter('md', $matchday)
            ->setParameter('now', new \DateTime())
            ->orderBy('g.date', $sort)
            ->getQuery()
            ->getArrayResult();
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
        string            $competition,
        string            $season,
        bool              $processed
    ): void {
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
        $game->setProcessed($processed);

        $em->persist($game);
        $em->flush();
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
            ->orderBy('g.competition', 'ASC')->orderBy('g.matchday', 'DESC')
            ->getQuery()->getResult();
    }

    /**
     * @throws \Exception
     */
    public function createOrUpdateGames(array $games): void
    {
        $em = $this->getEntityManager();

        /** @var GameDto $gameDto */
        foreach ($games as $gameDto) {
            $openLigaId  = $gameDto->getOpenLigaId();
            $homeId      = $gameDto->getHomeId();
            $awayId      = $gameDto->getAwayId();
            $homeScore   = $gameDto->getHomeScore();
            $awayScore   = $gameDto->getAwayScore();
            $date        = $gameDto->getDate();
            $matchday    = $gameDto->getMatchday();
            $competition = $gameDto->getCompetition();
            $season      = $gameDto->getSeason();
            $processed   = $gameDto->getProcessed();

            $existingGame = $this->findOneBy(['openLigaId' => $openLigaId]);

            if ($existingGame === null) {
                $this->create(
                    $openLigaId,
                    $homeId,
                    $awayId,
                    $homeScore,
                    $awayScore,
                    $date,
                    $matchday,
                    $competition,
                    $season,
                    $processed
                );
            } else {
                $existingGame->setHomeGoals($homeScore);
                $existingGame->setAwayGoals($awayScore);
                $existingGame->setDate($date);
                $em->persist($existingGame);
                $em->flush();
            }
        }
    }
}

