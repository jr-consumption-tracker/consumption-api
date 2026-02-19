<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use JR\Tracker\Entity\Billing\Implementation\Subscription;
use JR\Tracker\Entity\Measurement\Implementation\ConsumptionPlace;
use JR\Tracker\Entity\User\Contract\UserInterface;
use Symfony\Component\Uid\Ulid;

#[Entity]
#[Table(name: 'user')]
class User implements UserInterface
{
  #[Id]
  #[GeneratedValue(strategy: 'NONE')]
  #[Column(length: 26, unique: true)]
  private string $idUser;

  #[Column(length: 50, unique: true)]
  private string $email;

  #[Column(nullable: true)]
  /** @phpstan-ignore-next-line */
  private ?\DateTimeImmutable $emailVerifiedAt;

  #[Column(length: 255)]
  private string $password;

  #[Column(nullable: false)]
  private ?bool $isDisabled;

  #[Column(nullable: true)]
  private ?DateTime $webLoginRestrictedUntil;

  #[Column(nullable: true)]
  private ?DateTime $adminLoginRestrictedUntil;

  #[Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
  private \DateTimeImmutable $createdAt;

  #[OneToOne(mappedBy: 'user', targetEntity: UserInfo::class, cascade: ['persist', 'remove'])]
  private UserInfo $userInfo;

  #[OneToMany(mappedBy: 'user', targetEntity: UserRole::class, cascade: ['persist', 'remove'])]
  private Collection $userRole;

  #[OneToMany(mappedBy: 'user', targetEntity: UserPermissionOverride::class, cascade: ['persist', 'remove'])]
  private Collection $permissionOverride;

  #[OneToMany(mappedBy: 'user', targetEntity: Subscription::class, cascade: ['persist', 'remove'])]
  private Collection $subscription;

  #[OneToMany(mappedBy: 'user', targetEntity: ConsumptionPlace::class, cascade: ['persist', 'remove'])]
  private Collection $consumptionPlace;

  #[OneToMany(mappedBy: 'user', targetEntity: UserLoginHistory::class, cascade: ['persist', 'remove'])]
  private Collection $userLoginHistory;


  #[OneToMany(mappedBy: 'user', targetEntity: UserToken::class, cascade: ['persist', 'remove'])]
  private Collection $userToken;

  public function __construct()
  {
    $this->userRole = new ArrayCollection();
    $this->permissionOverride = new ArrayCollection();
    $this->subscription = new ArrayCollection();
    $this->consumptionPlace = new ArrayCollection();
    $this->userLoginHistory = new ArrayCollection();
    $this->userToken = new ArrayCollection();
  }

  // Getters
  public function getUuid(): string
  {
    return $this->idUser;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getEmailVerifiedAt(): ?\DateTimeImmutable
  {
    return $this->emailVerifiedAt;
  }

  public function getPassword(): string
  {
    return $this->password;
  }

  public function getIsDisabled(): ?bool
  {
    return $this->isDisabled;
  }

  public function getWebLoginRestrictedUntil(): ?DateTime
  {
    return $this->webLoginRestrictedUntil;
  }

  public function getAdminLoginRestrictedUntil(): ?DateTime
  {
    return $this->adminLoginRestrictedUntil;
  }

  public function getCreatedAt(): \DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function getUserInfo(): UserInfo
  {
    return $this->userInfo;
  }

  public function getUserRole(): Collection
  {
    return $this->userRole;
  }

  public function getPermissionOverride(): Collection
  {
    return $this->permissionOverride;
  }

  public function getSubscription(): Collection
  {
    return $this->subscription;
  }

  public function getConsumptionPlace(): Collection
  {
    return $this->consumptionPlace;
  }

  public function getUserLoginHistory(): Collection
  {
    return $this->userLoginHistory;
  }

  public function getUserToken(): Collection
  {
    return $this->userToken;
  }

  // Setters
  public function setUuid(): self
  {
    $this->idUser = Ulid::generate();

    return $this;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }

  public function setEmailVerifiedAt(): self
  {
    $this->emailVerifiedAt = new \DateTimeImmutable();

    return $this;
  }

  public function setPassword(string $hashedPassword): self
  {
    $this->password = $hashedPassword;

    return $this;
  }

  public function stIsDisabled(?bool $isDisabled): self
  {
    $this->isDisabled = $isDisabled;

    return $this;
  }

  public function setWebLoginRestrictedUntil(?DateTime $webLoginRestrictedUntil): self
  {
    $this->webLoginRestrictedUntil = $webLoginRestrictedUntil;

    return $this;
  }

  public function setAdminLoginRestrictedUntil(?DateTime $adminLoginRestrictedUntil): self
  {
    $this->adminLoginRestrictedUntil = $adminLoginRestrictedUntil;

    return $this;
  }

  public function setCreatedAt(): self
  {
    $this->createdAt = new \DateTimeImmutable();

    return $this;
  }

  public function setUserInfo(UserInfo $userInfo): self
  {
    $this->userInfo = $userInfo;

    return $this;
  }
}
