<?php

namespace App\Entity;

use App\Repository\FriendshipRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FriendshipRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_FRIENDSHIP_PAIR', fields: ['userLow', 'userHigh'])]
class Friendship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userLow = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userHigh = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function between(User $a, User $b): self
    {
        $friendship = new self();

        if ($a->getId() < $b->getId()) {
            $friendship->userLow = $a;
            $friendship->userHigh = $b;
        } else {
            $friendship->userLow = $b;
            $friendship->userHigh = $a;
        }

        return $friendship;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserLow(): ?User
    {
        return $this->userLow;
    }

    public function getUserHigh(): ?User
    {
        return $this->userHigh;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function involves(User $user): bool
    {
        return $this->userLow?->getId() === $user->getId()
            || $this->userHigh?->getId() === $user->getId();
    }

    public function getOtherUser(User $user): ?User
    {
        if ($this->userLow?->getId() === $user->getId()) {
            return $this->userHigh;
        }

        if ($this->userHigh?->getId() === $user->getId()) {
            return $this->userLow;
        }

        return null;
    }
}
