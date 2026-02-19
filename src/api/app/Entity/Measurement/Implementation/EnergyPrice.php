<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Measurement\Implementation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use JR\Tracker\Entity\Measurement\Contract\EnergyPriceInterface;

#[Entity]
#[Table(name: 'energyPrice')]
#[Index(columns: ['idMeasuredEnergy'])]
class EnergyPrice implements EnergyPriceInterface
{
  #[Id]
  #[GeneratedValue(strategy: 'AUTO')]
  #[Column]
  /** @phpstan-ignore-next-line */
  private int $idEnergyPrice;

  #[ManyToOne(targetEntity: MeasuredEnergy::class)]
  #[JoinColumn(name: "idMeasuredEnergy", referencedColumnName: "idMeasuredEnergy", nullable: false)]
  private MeasuredEnergy $measuredEnergy;

  #[Column(type: 'decimal', precision: 10, scale: 4)]
  private float $unitPrice;

  #[Column(type: 'date')]
  private \DateTimeImmutable $validFrom;

  #[OneToMany(mappedBy: 'energyPrice', targetEntity: EnergyPriceComponent::class, cascade: ['persist', 'remove'])]
  private Collection $component;

  public function __construct()
  {
    $this->component = new ArrayCollection();
  }

  // Getters
  public function getIdEnergyPrice(): int
  {
    return $this->idEnergyPrice;
  }

  public function getMeasuredEnergy(): MeasuredEnergy
  {
    return $this->measuredEnergy;
  }

  public function getUnitPrice(): float
  {
    return $this->unitPrice;
  }

  public function getValidFrom(): \DateTimeImmutable
  {
    return $this->validFrom;
  }

  public function getComponent(): Collection
  {
    return $this->component;
  }

  // Setters
  public function setMeasuredEnergy(MeasuredEnergy $measuredEnergy): self
  {
    $this->measuredEnergy = $measuredEnergy;

    return $this;
  }

  public function setUnitPrice(float $unitPrice): self
  {
    $this->unitPrice = $unitPrice;

    return $this;
  }

  public function setValidFrom(\DateTimeImmutable $validFrom): self
  {
    $this->validFrom = $validFrom;

    return $this;
  }
}
