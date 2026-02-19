<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Billing\Implementation;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
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

  // Getters
  public function getUseSubscriptionPlan(): SubscriptionPlan
  {
    return $this->useSubscriptionPlan;
  }

  public function getUserSubscriptionFeature(): SubscriptionFeature
  {
    return $this->userSubscriptionFeature;
  }

  // Setters
  public function setUseSubscriptionPlan(SubscriptionPlan $useSubscriptionPlan): self
  {
    $this->useSubscriptionPlan = $useSubscriptionPlan;

    return $this;
  }

  public function setUserSubscriptionFeature(SubscriptionFeature $userSubscriptionFeature): self
  {
    $this->userSubscriptionFeature = $userSubscriptionFeature;

    return $this;
  }
}
