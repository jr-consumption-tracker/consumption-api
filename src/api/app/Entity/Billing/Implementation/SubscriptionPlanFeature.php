<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use JR\Tracker\Entity\Billing\Contract\SubscriptionPlanFeatureInterface;

#[Entity]
#[Table(name: 'subscriptionPlanFeature')]
class SubscriptionPlanFeature implements SubscriptionPlanFeatureInterface
{
    #[Id]
    #[ManyToOne(targetEntity: SubscriptionPlan::class, inversedBy: 'userSubscriptionPlanFeature')]
    #[JoinColumn(name: 'idUserSubscriptionPlan', referencedColumnName: 'idUserSubscriptionPlan', nullable: false)]
    private SubscriptionPlan $useSubscriptionPlan;

    #[Id]
    #[ManyToOne(targetEntity: SubscriptionFeature::class)]
    #[JoinColumn(name: 'idUserSubscriptionFeature', referencedColumnName: 'idUserSubscriptionFeature', nullable: false)]
    private SubscriptionFeature $userSubscriptionFeature;
}