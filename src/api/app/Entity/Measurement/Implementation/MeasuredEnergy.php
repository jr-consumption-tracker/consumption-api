<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Measurement\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
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
}