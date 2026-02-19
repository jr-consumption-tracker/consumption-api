<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Measurement\Implementation;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JR\Tracker\Entity\Measurement\Contract\MeterReplacementInterface;

#[Entity]
#[Table(name: 'meterReplacement')]
#[Index(columns: ['idMeasuredEnergy', 'year', 'month'])]
class MeterReplacement implements MeterReplacementInterface
{
  #[Id]
  #[GeneratedValue(strategy: 'AUTO')]
  #[Column]
  /** @phpstan-ignore-next-line */
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

  // Getters
  public function getIdMeter(): int
  {
    return $this->idMeter;
  }

  public function getYear(): int
  {
    return $this->year;
  }

  public function getMonth(): int
  {
    return $this->month;
  }

  public function getOldMeterFinalValue(): float
  {
    return $this->oldMeterFinalValue;
  }

  public function getNewMeterInitialValue(): float
  {
    return $this->newMeterInitialValue;
  }

  public function getReplacedAt(): ?\DateTimeImmutable
  {
    return $this->replacedAt;
  }

  public function getMeasuredEnergy(): MeasuredEnergy
  {
    return $this->measuredEnergy;
  }

  // Setters
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

  public function setOldMeterFinalValue(float $oldMeterFinalValue): self
  {
    $this->oldMeterFinalValue = $oldMeterFinalValue;

    return $this;
  }

  public function setNewMeterInitialValue(float $newMeterInitialValue): self
  {
    $this->newMeterInitialValue = $newMeterInitialValue;

    return $this;
  }

  public function setReplacedAt(?\DateTimeImmutable $replacedAt): self
  {
    $this->replacedAt = $replacedAt;

    return $this;
  }

  public function setMeasuredEnergy(MeasuredEnergy $measuredEnergy): self
  {
    $this->measuredEnergy = $measuredEnergy;

    return $this;
  }
}
