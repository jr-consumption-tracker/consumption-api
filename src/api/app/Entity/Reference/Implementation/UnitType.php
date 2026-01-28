<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Reference\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\Reference\Contract\UnitTypeInterface;

#[Entity]
#[Table(name: 'unitType')]
class UnitType implements UnitTypeInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idUnitType;

    #[Column(unique: true, length: 10)]
    private string $code; // kWh, MWh, m3, l

    #[Column(length: 50)]
    private string $name; // Kilowatthodina, Megawatthodina, Kubík, Litr

    #[Column(type: 'decimal', precision: 10, scale: 5)]
    private float $conversionToBase;
    // převodní koeficient k základní jednotce EnergyType
    // např. pro MWh → kWh = 1000
}