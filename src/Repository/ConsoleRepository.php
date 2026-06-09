<?php

namespace App\Repository;

use App\Entity\Brand;
use App\Entity\Console;
use App\Entity\User;
use App\Model\CollectionListQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Console>
 */
class ConsoleRepository extends ServiceEntityRepository
{
    use ListQueryBuilderTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Console::class);
    }

    /** @return list<Console> */
    public function findByOwner(User $owner, ?CollectionListQuery $query = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.brand', 'b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner);

        if (null === $query) {
            return $qb->orderBy('c.name', 'ASC')->getQuery()->getResult();
        }

        $this->applyNameFilter($qb, 'c', $query);
        $this->applyBrandFilter($qb, 'b', $query);
        $this->applySort($qb, 'c', $query, 'sort_cv');

        return $qb->getQuery()->getResult();
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

    public function findOneByOwnerBrandAndNormalizedName(
        User $owner,
        Brand $brand,
        string $name,
        ?int $excludeId = null,
    ): ?Console {
        $qb = $this->createQueryBuilder('c')
            ->join('c.brand', 'b')
            ->where('b.owner = :owner')
            ->andWhere('b = :brand')
            ->andWhere('LOWER(c.name) = :name')
            ->setParameter('owner', $owner)
            ->setParameter('brand', $brand)
            ->setParameter('name', mb_strtolower(trim($name)))
            ->setMaxResults(1);

        if (null !== $excludeId) {
            $qb->andWhere('c.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /** @return list<Console> */
    public function findRecentByOwner(User $owner, int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.brand', 'b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
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
