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
use JR\Tracker\Entity\Measurement\Contract\MeterReplacementInterface;

#[Entity]
#[Table(name: 'meterReplacement')]
#[Index(columns: ['idMeasuredEnergy', 'year', 'month'])]
class MeterReplacement implements MeterReplacementInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idMeter;

    #[Column(type: 'smallint')]
    private int $year;

    #[Column(type: 'smallint')]
    private int $month;

    #[Column(type: 'decimal', precision: 12, scale: 3)]
    private float $oldMeterFinalValue;

    #[Column(type: 'decimal', precision: 12, scale: 3)]
    private float $newMeterInitialValue;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $replacedAt;

    #[ManyToOne(targetEntity: MeasuredEnergy::class)]
    #[JoinColumn(name: "idMeasuredEnergy", referencedColumnName: "idMeasuredEnergy", nullable: false)]
    private MeasuredEnergy $measuredEnergy;
}