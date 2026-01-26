<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JR\Tracker\Entity\User\Contract\UserSubscriptionPlanInterface;

#[Entity]
#[Table(name: 'userSubscriptionPlan')]
class UserSubscriptionPlan implements UserSubscriptionPlanInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idUserSubscriptionPlan;


    #[Column(unique: true, length: 50)]
    private string $code; // FREE, PREMIUM

    #[Column(length: 255)]
    private string $name;

    #[Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price;

    #[Column(type: 'integer')]
    private int $durationDays; // 0 = neomezeně

    #[OneToMany(mappedBy: 'useSubscriptionPlan', targetEntity: UserSubscriptionPlanFeature::class)]
    private Collection $useSubscriptionPlanFeature;

    #[OneToMany(mappedBy: 'userSubscriptionPlan', targetEntity: UserSubscription::class)]
    private Collection $userSubscription;

    public function __construct()
    {
        $this->useSubscriptionPlanFeature = new ArrayCollection();
        $this->userSubscription = new ArrayCollection();
    }
}