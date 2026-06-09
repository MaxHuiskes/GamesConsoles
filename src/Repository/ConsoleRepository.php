<?php

namespace App\Repository;

use App\Entity\Console;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Console>
 */
class ConsoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Console::class);
    }

    /** @return list<Console> */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.brand', 'b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByOwner(User $owner): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.brand', 'b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRandomForOwner(User $owner): ?Console
    {
        $count = $this->countByOwner($owner);

        if ($count === 0) {
            return null;
        }

        $offset = random_int(0, $count - 1);

        return $this->createQueryBuilder('c')
            ->join('c.brand', 'b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
