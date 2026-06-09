<?php

namespace App\Repository;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetToken>
 */
class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    public function findValidByToken(string $token): ?PasswordResetToken
    {
        $resetToken = $this->findOneBy(['token' => $token]);

        if ($resetToken === null || !$resetToken->isValid()) {
            return null;
        }

        return $resetToken;
    }

    public function invalidateActiveForUser(User $user): void
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.user = :user')
            ->andWhere('t.usedAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
