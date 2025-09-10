<?php

namespace App\Repository;

use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Club>
 */
class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }

    public function create(int $id, string $name, string $logoUrl): void
    {
        $club = new Club();
        $club->setOpenLigaId($id);
        $club->setName($name);
        $club->setLogoUrl($logoUrl);
        $this->getEntityManager()->persist($club);
        $this->getEntityManager()->flush();
    }
}
