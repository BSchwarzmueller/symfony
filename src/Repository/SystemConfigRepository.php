<?php

namespace App\Repository;

use App\Entity\SystemConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SystemConfig>
 */
class SystemConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemConfig::class);
    }

    public function getAll(): array
    {
        return $this->findBy([], ['configKey' => 'ASC']);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $config = $this->findOneBy(['configKey' => $key]);
        return $config?->getConfigValue() ?? $default;
    }

    public function set(string $key, ?string $value): void
    {
        $em = $this->getEntityManager();
        $config = $this->find($key) ?? (new SystemConfig())->setConfigKey($key);
        $config->setValue($value);
        $em->persist($config);
        $em->flush();
    }
}
