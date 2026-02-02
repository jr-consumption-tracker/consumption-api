<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Billing\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JR\Tracker\Entity\Billing\Contract\SubscriptionPlanInterface;

#[Entity]
#[Table(name: 'subscriptionPlan')]
class SubscriptionPlan implements SubscriptionPlanInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    /** @phpstan-ignore-next-line */
    private int $idUserSubscriptionPlan;


    #[Column(unique: true, length: 50)]
    private string $code; // FREE, PREMIUM

    #[Column(length: 255)]
    private string $name;

    #[Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price;

    #[Column(type: 'integer')]
    private int $durationDays; // 0 = neomezeně

    #[OneToMany(mappedBy: 'useSubscriptionPlan', targetEntity: SubscriptionPlanFeature::class)]
    private Collection $useSubscriptionPlanFeature;

    #[OneToMany(mappedBy: 'userSubscriptionPlan', targetEntity: Subscription::class)]
    private Collection $userSubscription;

    public function __construct()
    {
        $this->useSubscriptionPlanFeature = new ArrayCollection();
        $this->userSubscription = new ArrayCollection();
    }

    // Getters
    public function getIdUserSubscriptionPlan(): int
    {
        return $this->idUserSubscriptionPlan;
    }
    public function getCode(): string
    {
        return $this->code;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getPrice(): string
    {
        return $this->price;
    }
    public function getDurationDays(): int
    {
        return $this->durationDays;
    }
    public function getUseSubscriptionPlanFeature(): Collection
    {
        return $this->useSubscriptionPlanFeature;
    }
    public function getUserSubscription(): Collection
    {
        return $this->userSubscription;
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
    public function setPrice(string $price): self
    {
        $this->price = $price;
        return $this;
    }
    public function setDurationDays(int $durationDays): self
    {
        $this->durationDays = $durationDays;
        return $this;
    }
}