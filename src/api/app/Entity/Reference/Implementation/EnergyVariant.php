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
}