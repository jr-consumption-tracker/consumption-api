<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

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
    private int $idUserSubscriptionFeature;

    #[Column(unique: true, length: 50)]
    private string $code; // EXPORT_PDF, API_ACCESS

    #[Column(length: 50)]
    private string $description;
}