<?php

namespace App\Repository;

use App\Entity\Brand;
use App\Entity\User;
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
    public function findByOwner(User $owner): array
    {
        return $this->findBy(['owner' => $owner], ['name' => 'ASC']);
    }

    public function countByOwner(User $owner): int
    {
        return $this->count(['owner' => $owner]);
    }
}
