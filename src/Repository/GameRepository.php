<?php

namespace App\Repository;

use App\Dto\GameDto;
use App\Entity\Club;
use App\Entity\Game;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
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
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('homeGoals', -1))
            ->andWhere(Criteria::expr()->eq('awayGoals', -1))
            ->orderBy(['date' => Order::Ascending]);

        return $this->matching($criteria)->toArray();
    }

    public function getActiveGames(): array
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->gte('homeGoals', 0))
            ->andWhere(Criteria::expr()->gte('awayGoals', 0))
            ->orderBy(['date' => Order::Ascending]);

        return $this->matching($criteria)->toArray();
    }

    public function getPlayedGames(): array
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('processed', true))
            ->orderBy(['date' => Order::Ascending]);

        return $this->matching($criteria)->toArray();
    }

    /**
     * @throws \Exception
     */
    public function createOrUpdateGames(array $games): void
    {
        $em = $this->getEntityManager();

        /** @var GameDto $gameDto */
        foreach ($games as $gameDto) {

            $existingGame = $this->findOneBy(['openLigaId' => $gameDto->getOpenLigaId()]);

            if ($existingGame === null) {
                $this->create(
                    $gameDto->getOpenLigaId(),
                    $gameDto->getHomeId(),
                    $gameDto->getAwayId(),
                    $gameDto->getHomeScore(),
                    $gameDto->getAwayScore(),
                    $gameDto->getDate(),
                    $gameDto->getMatchday(),
                    $gameDto->getCompetition(),
                    $gameDto->getSeason(),
                    $gameDto->getProcessed()
                );
            } else {
                $existingGame->setHomeGoals($gameDto->getHomeScore());
                $existingGame->setAwayGoals($gameDto->getAwayScore());

                $em->persist($existingGame);
                $em->flush();
            }
        }
    }
}

