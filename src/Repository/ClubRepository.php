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

    public function createOrUpdate(int $id, string $name, string $shortName, string $logoUrl): void
    {
        $em = $this->getEntityManager();
        $club = $this->findOneBy(['openLigaId' => $id]);

        if ($club === null) {
            $club = new Club();
            $club->setOpenLigaId($id);
        }

        $club->setName($name);
        $club->setShortName($shortName);
        $club->setLogoUrl($logoUrl);

        $em->persist($club);
        $em->flush();
    }

    public function storeClubArray(array $clubs): void
    {
        if(empty($clubs)) {
            return;
        }

        foreach($clubs as $club) {
            $this->createOrUpdate(
                $club['teamId'],
                $club['teamName'],
                $club['shortName'],
                $club['teamIconUrl']
            );
        }
    }
}
