<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Reference\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JR\Tracker\Entity\Measurement\Implementation\EnergyPriceComponent;
use JR\Tracker\Entity\Reference\Contract\EnergyPriceComponentTypeInterface;

#[Entity]
#[Table(name: 'energyPriceComponentType')]
class EnergyPriceComponentType implements EnergyPriceComponentTypeInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    /** @phpstan-ignore-next-line */
    private int $idEnergyPriceComponentType;

    #[Column(length: 10, unique: true)]
    private string $code; // např. 'consumption', 'distribution', 'fixed_fee', 'tax'

    #[Column(length: 50, nullable: true)]
    private ?string $description = null;

    #[OneToMany(mappedBy: 'energyPriceComponentType', targetEntity: EnergyPriceComponent::class)]
    private Collection $component;

    public function __construct()
    {
        $this->component = new ArrayCollection();
    }


    // Getters
    public function getIdEnergyPriceComponentType(): int
    {
        return $this->idEnergyPriceComponentType;
    }
    public function getCode(): string
    {
        return $this->code;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function getComponent(): Collection
    {
        return $this->component;
    }


    // Setters
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}