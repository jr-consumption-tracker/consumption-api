<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Reference\Implementation;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use JR\Tracker\Entity\Reference\Contract\EnergyTypeInterface;

#[Entity]
#[Table(name: 'energyType')]
class EnergyType implements EnergyTypeInterface
{
  #[Id]
  #[GeneratedValue(strategy: 'AUTO')]
  #[Column]
  /** @phpstan-ignore-next-line */
  private int $idEnergyType;

  #[Column(length: 10, unique: true)]
  private string $code; // ELECTRICITY, WATER, GAS

  #[Column(length: 50)]
  private string $name; // Elektřina, Voda, Plyn

  #[Column(length: 25, nullable: true)]
  private ?string $variant; // VT, NT, studená, teplá

  // Getters
  public function getIdEnergyType(): int
  {
    return $this->idEnergyType;
  }

  public function getCode(): string
  {
    return $this->code;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getVariant(): ?string
  {
    return $this->variant;
  }

  // Setters
  public function setCode(string $code): self
  {
    $this->code = $code;

    return $this;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function setVariant(?string $variant): self
  {
    $this->variant = $variant;

    return $this;
  }
}
