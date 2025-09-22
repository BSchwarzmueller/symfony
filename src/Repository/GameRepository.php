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
    public function findArrayByMatchday(int $matchday, ?string $sort = 'ASC'): array
    {
        return $this->createQueryBuilder('g')
            // Passe die Property-Namen an dein Entity an:
            ->leftJoin('g.homeClub', 'hc')->addSelect('hc')
            ->leftJoin('g.awayClub', 'ac')->addSelect('ac')
            ->where('g.matchday = :md')
            ->setParameter('md', $matchday)
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
            ->orderBy('g.competition', 'ASC')->orderBy('g.matchday', 'DESC')
            ->getQuery()->getResult();
    }

    /**
     * @throws \Exception
     */
    public function createGames(array $games, int $matchday): void
    {
        foreach ($games as $game) {

            $openLigaId = $game['matchID'];

            $homeId = $game['team1']['teamId'];
            $awayId = $game['team2']['teamId'];

            $homeScore = !empty($game['matchResults'][1]) ? $game['matchResults'][1]['pointsTeam1'] : null;
            $awayScore = !empty($game['matchResults'][1]) ? $game['matchResults'][1]['pointsTeam2'] : null;

            $date = new DateTimeImmutable($game['matchDateTime']);

            $this->create($openLigaId, $homeId, $awayId, $homeScore, $awayScore, $date, $matchday);
        }
    }

    public function updateGames(array $games): void
    {
        $em = $this->getEntityManager();

        foreach ($games as $game) {
            $openLigaId = $game['matchID'] ?? null;
            if ($openLigaId === null) {
                continue;
            }

            /** @var Game|null $entity */
            $entity = $this->findOneBy(['openLigaId' => $openLigaId]);
            if ($entity === null) {
                // Optional: wenn Spiel noch nicht existiert, überspringen
                continue;
            }

            // Scores (falls vorhanden)
            $homeScore = !empty($game['matchResults'][1]) ? $game['matchResults'][1]['pointsTeam1'] : null;
            $awayScore = !empty($game['matchResults'][1]) ? $game['matchResults'][1]['pointsTeam2'] : null;

            // Datum (falls vorhanden)
            if (!empty($game['matchDateTime'])) {
                try {
                    $date = new DateTimeImmutable($game['matchDateTime']);
                    $entity->setDate($date);
                } catch (\Throwable) {
                    // Ignoriere ungültiges Datum
                }
            }

            // Tore aktualisieren
            $entity->setHomeGoals($homeScore);
            $entity->setAwayGoals($awayScore);

            // Optional: processed zurücksetzen, wenn Ergebnis noch fehlt
            if ($homeScore === null || $awayScore === null) {
                $entity->setProcessed(false);
            }

            $em->persist($entity);
        }

        $em->flush();
    }
}
