<?php

namespace App\Repository;

use App\Entity\UserStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserStats>
 */
class UserStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserStats::class);
    }

    public function store(UserStats $userStats): void
    {
        $this->getEntityManager()->persist($userStats);
        $this->getEntityManager()->flush();
    }

    public function getLeaders(): mixed
    {
        return $this->createQueryBuilder('us')
            ->join('us.User', 'u')
            ->leftJoin('u.userProfile', 'up')
            ->select('COALESCE(up.user, u.id) AS name, us.points')
            ->orderBy('us.points', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getArrayResult();
    }
}
