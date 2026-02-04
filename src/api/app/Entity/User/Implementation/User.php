<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Uid\Ulid;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\Billing\Implementation\Subscription;
use JR\Tracker\Entity\Measurement\Implementation\ConsumptionPlace;

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

    #[Column(length: 255)]
    private string $password;

    #[Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $createdAt;

    #[OneToOne(mappedBy: 'user', targetEntity: UserInfo::class, cascade: ['persist', 'remove'])]
    private UserInfo $userInfo;

    #[OneToMany(mappedBy: 'user', targetEntity: UserRole::class, cascade: ['persist', 'remove'])]
    private Collection $userRole;

    #[OneToMany(mappedBy: 'user', targetEntity: UserPermissionOverride::class, cascade: ['persist', 'remove'])]
    private Collection $permissionOverride;

    #[OneToMany(mappedBy: 'user', targetEntity: Subscription::class)]
    private Collection $subscription;

    #[OneToMany(mappedBy: 'user', targetEntity: ConsumptionPlace::class)]
    private Collection $consumptionPlace;

    public function __construct()
    {
        $this->userRole = new ArrayCollection();
        $this->permissionOverride = new ArrayCollection();
        $this->subscription = new ArrayCollection();
        $this->consumptionPlace = new ArrayCollection();
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
    public function getPassword(): string
    {
        return $this->password;
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
    public function setPassword(string $hashedPassword): self
    {
        $this->password = $hashedPassword;
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