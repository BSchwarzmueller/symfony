<?php

namespace App\Repository;

use App\Entity\SystemConfig;
use App\Service\CachingService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\InvalidArgumentException;

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

    public function get(string $key, ?string $default = null): SystemConfig|null
    {
        return $this->findOneBy(['configKey' => $key]);
    }

    public function set(string $key, ?string $value, CachingService $cache): void
    {
        $em     = $this->getEntityManager();
        $config = $this->find($key) ?? (new SystemConfig())->setConfigKey($key);
        $config->setValue($value);
        try {
            $em->persist($config);
            $em->flush();
            $cache->deleteConfig($key);
        } catch (Exception $e) {
            throw new \RuntimeException('Could not persist config: ' . $e->getMessage());
        }
    }
}
