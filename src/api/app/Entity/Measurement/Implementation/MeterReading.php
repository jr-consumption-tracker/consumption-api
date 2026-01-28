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
use JR\Tracker\Entity\Measurement\Contract\MeterReadingInterface;

#[Entity]
#[Table(name: 'meterReading')]
#[UniqueConstraint(fields: ['measuredEnergy', 'year', 'month'])]
class MeterReading implements MeterReadingInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idMeterReading;

    #[ManyToOne(targetEntity: MeasuredEnergy::class)]
    #[JoinColumn(name: "idMeasuredEnergy", referencedColumnName: "idMeasuredEnergy", nullable: false)]
    private MeasuredEnergy $measuredEnergy;

    #[Column]
    private int $year;

    #[Column]
    private int $month;

    #[Column(type: 'decimal', precision: 12, scale: 3)]
    private float $value;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $measuredAt;
}