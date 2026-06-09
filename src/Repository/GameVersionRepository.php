<?php

namespace App\Repository;

use App\Entity\GameVersion;
use App\Entity\User;
use App\Model\PlayablePickerQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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
            ->select('COUNT(v.id)')
            ->join('v.game', 'g')
            ->where('g.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<GameVersion> */
    public function findRecentByOwner(User $owner, int $limit): array
    {
        return $this->createQueryBuilder('v')
            ->join('v.game', 'g')
            ->where('g.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('v.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRandomForOwner(User $owner): ?GameVersion
    {
        return $this->findRandomPlayableForOwner($owner, new PlayablePickerQuery());
    }

    public function countPlayableForOwner(User $owner, PlayablePickerQuery $query): int
    {
        return (int) $this->createPlayableQueryBuilder($owner, $query)
            ->select('COUNT(DISTINCT v.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRandomPlayableForOwner(User $owner, PlayablePickerQuery $query): ?GameVersion
    {
        $count = $this->countPlayableForOwner($owner, $query);

        if ($count === 0) {
            return null;
        }

        $offset = random_int(0, $count - 1);

        return $this->createPlayableQueryBuilder($owner, $query)
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createPlayableQueryBuilder(User $owner, PlayablePickerQuery $query): QueryBuilder
    {
        $qb = $this->createQueryBuilder('v')
            ->join('v.game', 'g')
            ->where('g.owner = :owner')
            ->setParameter('owner', $owner);

        if (null !== $query->consoleId) {
            $qb->join('g.consoles', 'play_c')
                ->andWhere('play_c.id = :consoleId')
                ->setParameter('consoleId', $query->consoleId);
        }

        if (null !== $query->condition) {
            $qb->andWhere('v.condition = :condition')
                ->setParameter('condition', $query->condition);
        }

        return $qb;
    }
}
