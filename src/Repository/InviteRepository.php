<?php

namespace App\Repository;

use App\Entity\Invite;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @extends ServiceEntityRepository<Invite>
 */
class InviteRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        #[Autowire('%app.invite_expiry_days%')]
        private readonly int $inviteExpiryDays,
    ) {
        parent::__construct($registry, Invite::class);
    }

    public function getExpiryDays(): int
    {
        return $this->inviteExpiryDays;
    }

    public function findValidByToken(string $token): ?Invite
    {
        $invite = $this->findOneBy(['token' => $token]);

        if ($invite === null || !$invite->isOpen($this->inviteExpiryDays)) {
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
