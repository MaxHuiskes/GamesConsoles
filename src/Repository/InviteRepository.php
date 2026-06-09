<?php

namespace App\Repository;

use App\Entity\Invite;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invite>
 */
class InviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invite::class);
    }

    public function findValidByToken(string $token): ?Invite
    {
        $invite = $this->findOneBy(['token' => $token]);

        if ($invite === null || $invite->isUsed()) {
            return null;
        }

        return $invite;
    }

    /** @return list<Invite> */
    public function findByInviter(User $user): array
    {
        return $this->findBy(['invitedBy' => $user], ['createdAt' => 'DESC']);
    }
}
