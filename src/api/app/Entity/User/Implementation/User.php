<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use JR\Tracker\Entity\User\Contract\UserInterface;

#[Entity]
#[Table(name: 'user')]
class User implements UserInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idUser;

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

    #[OneToMany(mappedBy: 'user', targetEntity: UserSubscription::class)]
    private Collection $subscription;

    // #[OneToMany(mappedBy: 'user', targetEntity: ConsumptionPlace::class)]
    // private Collection $consumptionPlace;

    public function __construct()
    {
        $this->userRole = new ArrayCollection();
        $this->permissionOverride = new ArrayCollection();
        $this->subscription = new ArrayCollection();
        // $this->consumptionPlace = new ArrayCollection();
    }
}