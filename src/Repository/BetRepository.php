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
    public function store(Bet $bet): bool
    {
        $em = $this->getEntityManager();

        try {
            $userRef = $bet->getUser();;
            $gameRef = $bet->getGame();;

            $existingBet = $this->findOneBy([
                'userId' => $userRef,
                'gameId' => $gameRef,
            ]);

            if($existingBet instanceof Bet) {
                $existingBet->setHomeGoals($bet->getHomeGoals());
                $existingBet->setAwayGoals($bet->getAwayGoals());;
                $em->flush();
                return true;
            }

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
