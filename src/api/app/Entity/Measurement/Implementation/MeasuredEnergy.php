<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Measurement\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\UniqueConstraint;
use JR\Tracker\Entity\Reference\Implementation\UnitType;
use JR\Tracker\Entity\Reference\Implementation\EnergyType;
use JR\Tracker\Entity\Reference\Implementation\EnergyVariant;
use JR\Tracker\Entity\Measurement\Contract\MeasuredEnergyInterface;

#[Entity]
#[Table(name: 'measuredEnergy')]
#[UniqueConstraint(fields: ['consumptionPlace', 'energyType'])]
class MeasuredEnergy implements MeasuredEnergyInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    /** @phpstan-ignore-next-line */
    private int $idMeasuredEnergy;

    #[ManyToOne(targetEntity: ConsumptionPlace::class)]
    #[JoinColumn(name: "idConsumptionPlace", referencedColumnName: "idConsumptionPlace", nullable: false)]
    private ConsumptionPlace $consumptionPlace;

    #[ManyToOne(targetEntity: EnergyType::class)]
    #[JoinColumn(name: "idEnergyType", referencedColumnName: "idEnergyType", nullable: false)]
    private EnergyType $energyType;

    #[ManyToOne(targetEntity: UnitType::class)]
    #[JoinColumn(name: "idUnitType", referencedColumnName: "idUnitType", nullable: false)]
    private UnitType $unitType;

    #[ManyToOne(targetEntity: EnergyVariant::class)]
    #[JoinColumn(name: "idEnergyVariant", referencedColumnName: "idEnergyVariant", nullable: true)]
    private ?EnergyVariant $energyVariant;

    #[OneToOne(targetEntity: EnergyPrice::class, mappedBy: 'measuredEnergy', cascade: ['persist', 'remove'])]
    private ?EnergyPrice $price;


    // Getters
    public function getIdMeasuredEnergy(): int
    {
        return $this->idMeasuredEnergy;
    }
    public function getConsumptionPlace(): ConsumptionPlace
    {
        return $this->consumptionPlace;
    }
    public function getEnergyType(): EnergyType
    {
        return $this->energyType;
    }
    public function getUnitType(): UnitType
    {
        return $this->unitType;
    }
    public function getEnergyVariant(): ?EnergyVariant
    {
        return $this->energyVariant;
    }
    public function getPrice(): ?EnergyPrice
    {
        return $this->price;
    }


    // Setters
    public function setConsumptionPlace(ConsumptionPlace $consumptionPlace): self
    {
        $this->consumptionPlace = $consumptionPlace;
        return $this;
    }
    public function setEnergyType(EnergyType $energyType): self
    {
        $this->energyType = $energyType;
        return $this;
    }
    public function setUnitType(UnitType $unitType): self
    {
        $this->unitType = $unitType;
        return $this;
    }
    public function setEnergyVariant(?EnergyVariant $energyVariant): self
    {
        $this->energyVariant = $energyVariant;
        return $this;
    }
    public function setPrice(?EnergyPrice $price): self
    {
        $this->price = $price;
        return $this;
    }
}