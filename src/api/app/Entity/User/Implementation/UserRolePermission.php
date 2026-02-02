<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use JR\Tracker\Entity\User\Contract\UserRolePermissionInterface;

#[Entity]
#[Table(name: 'userRolePermission')]
class UserRolePermission implements UserRolePermissionInterface
{
    #[Id]
    #[ManyToOne(targetEntity: UserRoleType::class, inversedBy: 'userRolePermissions')]
    #[JoinColumn(name: 'idUserRoleType', referencedColumnName: 'idUserRoleType', nullable: false)]
    private UserRoleType $userRoleType;

    #[Id]
    #[ManyToOne(targetEntity: UserPermission::class)]
    #[JoinColumn(name: 'idUserPermission', referencedColumnName: 'idUserPermission', nullable: false)]
    private UserPermission $userPermission;


    // Getters
    public function getUserRoleType(): UserRoleType
    {
        return $this->userRoleType;
    }
    public function getUserPermission(): UserPermission
    {
        return $this->userPermission;
    }


    // Setters
    public function setUserRoleType(UserRoleType $userRoleType): self
    {
        $this->userRoleType = $userRoleType;
        return $this;
    }
    public function setUserPermission(UserPermission $userPermission): self
    {
        $this->userPermission = $userPermission;
        return $this;
    }
}