<?php

namespace App\Repository;

use App\Entity\Console;
use App\Entity\Game;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /** @return list<Game> */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<Game> */
    public function findByConsoleForOwner(Console $console, User $owner): array
    {
        return $this->createQueryBuilder('g')
            ->distinct()
            ->join('g.consoles', 'c')
            ->join('c.brand', 'b')
            ->where('c = :console')
            ->andWhere('b.owner = :owner')
            ->setParameter('console', $console)
            ->setParameter('owner', $owner)
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByOwner(User $owner): int
    {
        return $this->count(['owner' => $owner]);
    }
}
