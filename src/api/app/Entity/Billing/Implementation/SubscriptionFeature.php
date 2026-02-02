<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Billing\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\Billing\Contract\SubscriptionFeatureInterface;

#[Entity]
#[Table(name: 'subscriptionFeature')]
class SubscriptionFeature implements SubscriptionFeatureInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    /** @phpstan-ignore-next-line */
    private int $idUserSubscriptionFeature;

    #[Column(unique: true, length: 25)]
    private string $code; // EXPORT_PDF, API_ACCESS

    #[Column(length: 50)]
    private string $description;


    // Getters
    public function getIdUserSubscriptionFeature(): int
    {
        return $this->idUserSubscriptionFeature;
    }
    public function getCode(): string
    {
        return $this->code;
    }
    public function getDescription(): string
    {
        return $this->description;
    }


    // Setters
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
}