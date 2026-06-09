<?php

namespace App\Repository;

use App\Entity\Console;
use App\Entity\Game;
use App\Entity\User;
use App\Model\CollectionListQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    use ListQueryBuilderTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /** @return list<Game> */
    public function findByOwner(User $owner, ?CollectionListQuery $query = null): array
    {
        $qb = $this->createQueryBuilder('g')
            ->where('g.owner = :owner')
            ->setParameter('owner', $owner);

        if (null === $query) {
            return $qb->orderBy('g.name', 'ASC')->getQuery()->getResult();
        }

        $this->applyNameFilter($qb, 'g', $query);

        $needsDistinct = false;

        if (null !== $query->brandId) {
            $qb->join('g.consoles', 'list_c')
                ->join('list_c.brand', 'list_b')
                ->andWhere('list_b.owner = :owner');
            $this->applyBrandFilter($qb, 'list_b', $query);
            $needsDistinct = true;
        }

        if (null !== $query->tagId) {
            $qb->join('g.tags', 'list_t')
                ->andWhere('list_t.owner = :owner')
                ->andWhere('list_t.id = :list_tag')
                ->setParameter('list_tag', $query->tagId);
            $needsDistinct = true;
        }

        if (null !== $query->condition) {
            $qb->join('g.versions', 'list_gv')
                ->andWhere('list_gv.condition = :list_condition')
                ->setParameter('list_condition', $query->condition);
            $needsDistinct = true;
        }

        if ($needsDistinct) {
            $qb->distinct();
        }

        $this->applySort($qb, 'g', $query, 'sort_gv');

        return $qb->getQuery()->getResult();
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

    /** @return list<Game> */
    public function findRecentByOwner(User $owner, int $limit): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('g.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
