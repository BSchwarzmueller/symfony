<?php

namespace App\Repository;

use App\Entity\Bet;
use App\Entity\Game;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class BetRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly GameRepository $gameRepository,
    ) {
        parent::__construct($registry, Bet::class);
    }

    /**
     * @throws \RuntimeException
     */
    public function store(int $userId, int $gameId, int $homeGoals, int $awayGoals, string $status): bool
    {
        $em = $this->getEntityManager();

        try {
            $userRef = $em->getReference(User::class, $userId);
            $gameRef = $em->getReference(Game::class, $gameId);

            $existingBet = $this->findOneBy([
                'userId' => $userRef,
                'gameId' => $gameRef,
            ]);

            if($existingBet instanceof Bet) {
                $existingBet->setHomeGoals($homeGoals);
                $existingBet->setAwayGoals($awayGoals);
                $em->flush();
                return true;
            }

            $bet = new Bet();
            $bet->setUserId($userRef);
            $bet->setGameId($gameRef);
            $bet->setHomeGoals($homeGoals);
            $bet->setAwayGoals($awayGoals);
            $bet->setStatus($status);
            $bet->setCreatedAt(new DateTimeImmutable());

            $em->persist($bet);
            $em->flush();

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('Bet create failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException($e->getMessage(), previous: $e);
        }
    }

    public function findArrayCurrentPlayerBet(int $currentMatchday, int $userId): array
    {
        $games = $this->gameRepository->findBy(['matchday' => $currentMatchday]);
        $gamesIds = array_map(fn(Game $game) => $game->getId(), $games);

        return $this->findBy(['userId' => $userId, 'gameId' => $gamesIds]);
    }

    /**
     * @throws ORMException
     */
    public function getBetArrayByUser(int $userId): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.userId', 'u')
            ->innerJoin('b.gameId', 'g')->addSelect('g')
            ->leftJoin('g.homeClub', 'hc')->addSelect('hc')
            ->leftJoin('g.awayClub', 'ac')->addSelect('ac')
            ->where('u.id = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('b.createdAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getArrayResult();
    }
}
