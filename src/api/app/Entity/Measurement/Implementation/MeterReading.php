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
    /** @phpstan-ignore-next-line */
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


    // Getters
    public function getIdMeterReading(): int
    {
        return $this->idMeterReading;
    }
    public function getMeasuredEnergy(): MeasuredEnergy
    {
        return $this->measuredEnergy;
    }
    public function getYear(): int
    {
        return $this->year;
    }
    public function getMonth(): int
    {
        return $this->month;
    }
    public function getValue(): float
    {
        return $this->value;
    }
    public function getMeasuredAt(): ?\DateTimeImmutable
    {
        return $this->measuredAt;
    }


    // Setters
    public function setMeasuredEnergy(MeasuredEnergy $measuredEnergy): self
    {
        $this->measuredEnergy = $measuredEnergy;
        return $this;
    }
    public function setYear(int $year): self
    {
        $this->year = $year;
        return $this;
    }
    public function setMonth(int $month): self
    {
        $this->month = $month;
        return $this;
    }
    public function setValue(float $value): self
    {
        $this->value = $value;
        return $this;
    }
    public function setMeasuredAt(?\DateTimeImmutable $measuredAt): self
    {
        $this->measuredAt = $measuredAt;
        return $this;
    }
}