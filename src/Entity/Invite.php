<?php

namespace App\Entity;

use App\Repository\InviteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InviteRepository::class)]
class Invite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $token = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $invitedBy = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    #[ORM\ManyToOne]
    private ?User $usedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->token = bin2hex(random_bytes(32));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getInvitedBy(): ?User
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?User $invitedBy): static
    {
        $this->invitedBy = $invitedBy;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUsedAt(): ?\DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function isUsed(): bool
    {
        return $this->usedAt !== null;
    }

    public function isExpired(int $expiryDays): bool
    {
        if ($expiryDays <= 0) {
            return false;
        }

        return $this->createdAt < new \DateTimeImmutable(sprintf('-%d days', $expiryDays));
    }

    public function isOpen(int $expiryDays): bool
    {
        return !$this->isUsed() && !$this->isExpired($expiryDays);
    }

    public function expiresAt(int $expiryDays): ?\DateTimeImmutable
    {
        if ($expiryDays <= 0) {
            return null;
        }

        return $this->createdAt->modify(sprintf('+%d days', $expiryDays));
    }

    public function getUsedBy(): ?User
    {
        return $this->usedBy;
    }

    public function markUsed(User $user): static
    {
        $this->usedAt = new \DateTimeImmutable();
        $this->usedBy = $user;

        return $this;
    }
}
