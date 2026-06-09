<?php

namespace App\Repository;

use App\Entity\Brand;
use App\Entity\User;
use App\Model\CollectionListQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Brand>
 */
class BrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    /** @return list<Brand> */
    public function findByOwner(User $owner, ?CollectionListQuery $query = null): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner);

        if (null === $query) {
            return $qb->orderBy('b.name', 'ASC')->getQuery()->getResult();
        }

        if ('' !== $query->q) {
            $qb->andWhere('LOWER(b.name) LIKE :list_q')
                ->setParameter('list_q', '%'.mb_strtolower($query->q).'%');
        }

        $direction = 'desc' === $query->dir ? 'DESC' : 'ASC';
        $qb->orderBy('b.name', $direction);

        return $qb->getQuery()->getResult();
    }

    public function countByOwner(User $owner): int
    {
        return $this->count(['owner' => $owner]);
    }

    /** @return list<Brand> */
    public function findRecentByOwner(User $owner, int $limit): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('b.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
