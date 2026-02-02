<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Reference\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\UniqueConstraint;
use JR\Tracker\Entity\Reference\Contract\EnergyVariantInterface;

#[Entity]
#[Table(name: 'energyVariant')]
#[UniqueConstraint(columns: ['IdEnergyType', 'code'])]
class EnergyVariant implements EnergyVariantInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    /** @phpstan-ignore-next-line */
    private int $idEnergyVariant;

    #[ManyToOne(targetEntity: EnergyType::class, inversedBy: 'energyVariant')]
    #[JoinColumn(name: 'idEnergyType', referencedColumnName: 'idEnergyType', nullable: false)]
    private EnergyType $energyType;

    #[Column(length: 10)]
    private string $code; // např.: VT, NT, COLD, HOT

    #[Column(length: 50)]
    private string $name; // např.: Vysoký tarif, Nízký tarif, Studená voda

    #[Column(type: 'smallint')]
    private int $sortOrder;

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $active;


    // Getters
    public function getIdEnergyVariant(): int
    {
        return $this->idEnergyVariant;
    }
    public function getEnergyType(): EnergyType
    {
        return $this->energyType;
    }
    public function getCode(): string
    {
        return $this->code;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }
    public function isActive(): bool
    {
        return $this->active;
    }


    // Setters
    public function setEnergyType(EnergyType $energyType): self
    {
        $this->energyType = $energyType;
        return $this;
    }
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
}