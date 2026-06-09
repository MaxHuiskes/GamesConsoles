<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /** @var Collection<int, Console> */
    #[ORM\ManyToMany(targetEntity: Console::class, inversedBy: 'games')]
    #[ORM\JoinTable(name: 'game_console')]
    private Collection $consoles;

    /** @var Collection<int, GameVersion> */
    #[ORM\OneToMany(targetEntity: GameVersion::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $versions;

    public function __construct()
    {
        $this->consoles = new ArrayCollection();
        $this->versions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /** @return Collection<int, Console> */
    public function getConsoles(): Collection
    {
        return $this->consoles;
    }

    public function addConsole(Console $console): static
    {
        if (!$this->consoles->contains($console)) {
            $this->consoles->add($console);
        }

        return $this;
    }

    public function removeConsole(Console $console): static
    {
        $this->consoles->removeElement($console);

        return $this;
    }

    /** @return Collection<int, GameVersion> */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function addVersion(GameVersion $version): static
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setGame($this);
        }

        return $this;
    }

    public function removeVersion(GameVersion $version): static
    {
        if ($this->versions->removeElement($version)) {
            if ($version->getGame() === $this) {
                $version->setGame(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
