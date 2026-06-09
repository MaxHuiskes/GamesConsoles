<?php

namespace App\Repository;

use App\Entity\ConsoleVersion;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConsoleVersion>
 */
class ConsoleVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsoleVersion::class);
    }

    public function countByOwner(User $owner): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->join('v.console', 'c')
            ->join('c.brand', 'b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<ConsoleVersion> */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('version')
            ->join('version.console', 'console')
            ->join('console.brand', 'brand')
            ->where('brand.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('console.name', 'ASC')
            ->addOrderBy('version.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<ConsoleVersion> */
    public function findRecentByOwner(User $owner, int $limit): array
    {
        return $this->createQueryBuilder('version')
            ->join('version.console', 'console')
            ->join('console.brand', 'brand')
            ->where('brand.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('version.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return array<int, int> version id => console id */
    public function getConsoleIdMapForOwner(User $owner): array
    {
        $map = [];

        foreach ($this->findByOwner($owner) as $version) {
            $consoleId = $version->getConsole()?->getId();
            if ($version->getId() !== null && $consoleId !== null) {
                $map[$version->getId()] = $consoleId;
            }
        }

        return $map;
    }
}
