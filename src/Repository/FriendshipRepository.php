<?php

namespace App\Repository;

use App\Entity\Friendship;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friendship>
 */
class FriendshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friendship::class);
    }

    public function areFriends(User $a, User $b): bool
    {
        if ($a->getId() === $b->getId()) {
            return false;
        }

        return $this->findFriendship($a, $b) !== null;
    }

    public function findFriendship(User $a, User $b): ?Friendship
    {
        [$low, $high] = $this->orderUsers($a, $b);

        return $this->findOneBy([
            'userLow' => $low,
            'userHigh' => $high,
        ]);
    }

    public function connect(User $a, User $b): Friendship
    {
        $existing = $this->findFriendship($a, $b);
        if ($existing !== null) {
            return $existing;
        }

        $friendship = Friendship::between($a, $b);
        $this->getEntityManager()->persist($friendship);
        $this->getEntityManager()->flush();

        return $friendship;
    }

    /** @return list<User> */
    public function findFriends(User $user): array
    {
        $friendships = $this->createQueryBuilder('f')
            ->where('f.userLow = :user OR f.userHigh = :user')
            ->setParameter('user', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(
            fn (Friendship $friendship) => $friendship->getOtherUser($user),
            $friendships
        );
    }

    /** @return array{0: User, 1: User} */
    private function orderUsers(User $a, User $b): array
    {
        return $a->getId() < $b->getId() ? [$a, $b] : [$b, $a];
    }
}
