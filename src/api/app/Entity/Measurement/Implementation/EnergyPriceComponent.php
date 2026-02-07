<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Measurement\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\Reference\Implementation\EnergyPriceComponentType;
use JR\Tracker\Entity\Measurement\Contract\EnergyPriceComponentInterface;

#[Entity]
#[Table(name: 'energyPriceComponent')]
#[Index(columns: ['idEnergyPrice'])]
class EnergyPriceComponent implements EnergyPriceComponentInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    /** @phpstan-ignore-next-line */
    private int $idEnergyPriceComponent;

    #[ManyToOne(targetEntity: EnergyPrice::class, inversedBy: 'components')]
    #[JoinColumn(name: "idEnergyPrice", referencedColumnName: "idEnergyPrice", nullable: false)]
    private EnergyPrice $energyPrice;

    #[ManyToOne(targetEntity: EnergyPriceComponentType::class)]
    #[JoinColumn(name: "idEnergyPriceComponentType", referencedColumnName: "idEnergyPriceComponentType", nullable: false)]
    private EnergyPriceComponentType $energyPriceComponentType;

    #[Column(type: 'decimal', precision: 10, scale: 4)]
    private float $value;

    #[Column(type: 'boolean')]
    private bool $perUnit; // true = násobíme spotřebou, false = fixní částka

    #[Column(type: 'date')]
    private \DateTimeImmutable $validFrom;


    // Getters
    public function getIdEnergyPriceComponent(): int
    {
        return $this->idEnergyPriceComponent;
    }
    public function getEnergyPrice(): EnergyPrice
    {
        return $this->energyPrice;
    }
    public function getEnergyPriceComponentType(): EnergyPriceComponentType
    {
        return $this->energyPriceComponentType;
    }
    public function getValue(): float
    {
        return $this->value;
    }
    public function isPerUnit(): bool
    {
        return $this->perUnit;
    }
    public function getValidFrom(): \DateTimeImmutable
    {
        return $this->validFrom;
    }


    // Setters
    public function setEnergyPrice(EnergyPrice $energyPrice): self
    {
        $this->energyPrice = $energyPrice;
        return $this;
    }
    public function setEnergyPriceComponentType(EnergyPriceComponentType $energyPriceComponentType): self
    {
        $this->energyPriceComponentType = $energyPriceComponentType;
        return $this;
    }
    public function setValue(float $value): self
    {
        $this->value = $value;
        return $this;
    }
    public function setPerUnit(bool $perUnit): self
    {
        $this->perUnit = $perUnit;
        return $this;
    }
    public function setValidFrom(\DateTimeImmutable $validFrom): self
    {
        $this->validFrom = $validFrom;
        return $this;
    }
}