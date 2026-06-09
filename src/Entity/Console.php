<?php

namespace App\Entity;

use App\Repository\ConsoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsoleRepository::class)]
#[ORM\Table(name: '`console`')]
class Console
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'consoles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Brand $brand = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /** @var Collection<int, Game> */
    #[ORM\ManyToMany(targetEntity: Game::class, mappedBy: 'consoles')]
    private Collection $games;

    /** @var Collection<int, ConsoleVersion> */
    #[ORM\OneToMany(targetEntity: ConsoleVersion::class, mappedBy: 'console', orphanRemoval: true)]
    private Collection $versions;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->versions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): static
    {
        $this->brand = $brand;

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

    /** @return Collection<int, Game> */
    public function getGames(): Collection
    {
        return $this->games;
    }

    /** @return Collection<int, ConsoleVersion> */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function addVersion(ConsoleVersion $version): static
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setConsole($this);
        }

        return $this;
    }

    public function removeVersion(ConsoleVersion $version): static
    {
        if ($this->versions->removeElement($version)) {
            if ($version->getConsole() === $this) {
                $version->setConsole(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
