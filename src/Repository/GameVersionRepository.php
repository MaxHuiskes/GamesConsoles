<?php

namespace App\Repository;

use App\Entity\GameVersion;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameVersion>
 */
class GameVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameVersion::class);
    }

    public function countByOwner(User $owner): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(DISTINCT v.id)')
            ->join('v.game', 'g')
            ->join('g.consoles', 'c')
            ->join('c.brand', 'b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRandomForOwner(User $owner): ?GameVersion
    {
        $count = $this->countByOwner($owner);

        if ($count === 0) {
            return null;
        }

        $offset = random_int(0, $count - 1);

        return $this->createQueryBuilder('v')
            ->distinct()
            ->join('v.game', 'g')
            ->join('g.consoles', 'c')
            ->join('c.brand', 'b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
