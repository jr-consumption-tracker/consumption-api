<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use JR\Tracker\Entity\User\Contract\UserSubscriptionPlanFeatureInterface;

#[Entity]
#[Table(name: 'userSubscriptionPlanFeature')]
class UserSubscriptionPlanFeature implements UserSubscriptionPlanFeatureInterface
{
    #[Id]
    #[ManyToOne(targetEntity: UserSubscriptionPlan::class, inversedBy: 'userSubscriptionPlanFeature')]
    #[JoinColumn(name: 'idUserSubscriptionPlan', referencedColumnName: 'idUserSubscriptionPlan', nullable: false)]
    private UserSubscriptionPlan $useSubscriptionPlan;

    #[Id]
    #[ManyToOne(targetEntity: UserSubscriptionFeature::class)]
    #[JoinColumn(name: 'idUserSubscriptionFeature', referencedColumnName: 'idUserSubscriptionFeature', nullable: false)]
    private UserSubscriptionFeature $userSubscriptionFeature;
}