<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\GeneratedValue;
use JR\Tracker\Entity\Billing\Contract\SubscriptionInterface;

#[Entity]
#[Table(name: 'subscription')]
class Subscription implements SubscriptionInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idUserSubscription;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $validFrom;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $validTo = null;

    #[Column(type: 'boolean')]
    private bool $isActive = true;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'userSubscription')]
    #[JoinColumn(name: 'idUser', referencedColumnName: 'idUser', nullable: false)]
    private User $user;

    #[ManyToOne(targetEntity: SubscriptionPlan::class, inversedBy: 'userSubscription')]
    #[JoinColumn(name: 'idUserSubscriptionPlan', referencedColumnName: 'idUserSubscriptionPlan', nullable: false)]
    private SubscriptionPlan $userSubscriptionPlan;
}