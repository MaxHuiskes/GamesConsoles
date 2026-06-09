<?php

namespace App\Entity;

use App\Repository\ConsoleVersionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsoleVersionRepository::class)]
#[ORM\Table(name: 'console_version')]
#[ORM\UniqueConstraint(name: 'UNIQ_CONSOLE_VERSION_NAME', fields: ['console', 'name'])]
class ConsoleVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Console $console = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(name: '`condition`', length: 50)]
    private ?string $condition = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prijs = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private mixed $foto = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConsole(): ?Console
    {
        return $this->console;
    }

    public function setConsole(?Console $console): static
    {
        $this->console = $console;

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

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(string $condition): static
    {
        $this->condition = $condition;

        return $this;
    }

    public function getPrijs(): ?string
    {
        return $this->prijs;
    }

    public function setPrijs(?string $prijs): static
    {
        $this->prijs = $prijs;

        return $this;
    }

    public function getFoto(): mixed
    {
        return $this->foto;
    }

    public function setFoto(mixed $foto): static
    {
        $this->foto = $foto;

        return $this;
    }

    public function hasFoto(): bool
    {
        return $this->foto !== null;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
