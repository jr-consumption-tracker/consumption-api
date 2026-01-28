<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Reference\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\Reference\Contract\EnergyTypeInterface;

#[Entity]
#[Table(name: 'energyType')]
class EnergyType implements EnergyTypeInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idEnergyType;

    #[Column(length: 10, unique: true)]
    private string $code; // ELECTRICITY, WATER, GAS

    #[Column(length: 50)]
    private string $name; // Elektřina, Voda, Plyn

    #[Column(length: 25, nullable: true)]
    private ?string $variant; // VT, NT, studená, teplá
}