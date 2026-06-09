<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_TAG_OWNER_NAME', fields: ['owner', 'name'])]
class Tag
{
    use CreatedAtTrait;

    public const DEFAULT_NAMES = ['co-op', 'backlog', 'completed'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 64)]
    private ?string $name = null;

    /** @var Collection<int, Game> */
    #[ORM\ManyToMany(targetEntity: Game::class, mappedBy: 'tags')]
    private Collection $games;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->initCreatedAt();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
