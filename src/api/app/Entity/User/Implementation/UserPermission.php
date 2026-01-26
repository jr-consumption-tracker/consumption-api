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
use JR\Tracker\Entity\User\Contract\UserPermissionInterface;

#[Entity]
#[Table(name: 'userPermission')]
class UserPermission implements UserPermissionInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idUserPermission;

    #[Column(length: 50, unique: true)]
    private string $code;

    #[Column(length: 50)]
    private string $description;

    #[Column(type: "smallint", unique: true)]
    private int $value;

    #[OneToMany(mappedBy: 'userPermission', targetEntity: UserRolePermission::class)]
    private Collection $userRolePermission;

    #[OneToMany(mappedBy: 'userPermission', targetEntity: UserPermissionOverride::class)]
    private Collection $userPermissionOverride;

    public function __construct()
    {
        $this->userRolePermission = new ArrayCollection();
        $this->userPermissionOverride = new ArrayCollection();
    }
}