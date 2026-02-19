<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\Billing\Implementation;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JR\Tracker\Entity\Billing\Contract\SubscriptionInterface;
use JR\Tracker\Entity\User\Implementation\User;

#[Entity]
#[Table(name: 'subscription')]
class Subscription implements SubscriptionInterface
{
  #[Id]
  #[GeneratedValue(strategy: 'AUTO')]
  #[Column]
  /** @phpstan-ignore-next-line */
  private int $idUserSubscription;

  #[Column(type: 'datetime_immutable')]
  private \DateTimeImmutable $validFrom;

  #[Column(type: 'datetime_immutable', nullable: true)]
  private ?\DateTimeImmutable $validTo;

  #[Column(type: 'boolean')]
  private bool $isActive;

  #[ManyToOne(targetEntity: User::class, inversedBy: 'userSubscription')]
  #[JoinColumn(name: 'idUser', referencedColumnName: 'idUser', nullable: false)]
  private User $user;

  #[ManyToOne(targetEntity: SubscriptionPlan::class, inversedBy: 'userSubscription')]
  #[JoinColumn(name: 'idUserSubscriptionPlan', referencedColumnName: 'idUserSubscriptionPlan', nullable: false)]
  private SubscriptionPlan $userSubscriptionPlan;

  // Getters
  public function getIdUserSubscription(): int
  {
    return $this->idUserSubscription;
  }

  public function getValidFrom(): \DateTimeImmutable
  {
    return $this->validFrom;
  }

  public function getValidTo(): ?\DateTimeImmutable
  {
    return $this->validTo;
  }

  public function getIsActive(): bool
  {
    return $this->isActive;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function getUserSubscriptionPlan(): SubscriptionPlan
  {
    return $this->userSubscriptionPlan;
  }

  // Setters
  public function setValidFrom(\DateTimeImmutable $validFrom): self
  {
    $this->validFrom = $validFrom;

    return $this;
  }

  public function setValidTo(?\DateTimeImmutable $validTo): self
  {
    $this->validTo = $validTo;

    return $this;
  }

  public function setIsActive(bool $isActive): self
  {
    $this->isActive = $isActive;

    return $this;
  }

  public function setUser(User $user): self
  {
    $this->user = $user;

    return $this;
  }

  public function setUserSubscriptionPlan(SubscriptionPlan $userSubscriptionPlan): self
  {
    $this->userSubscriptionPlan = $userSubscriptionPlan;

    return $this;
  }
}
