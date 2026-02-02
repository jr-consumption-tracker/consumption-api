<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use JR\Tracker\Entity\User\Contract\UserPermissionOverrideInterface;

#[Entity]
#[Table(name: 'userPermissionOverride')]
class UserPermissionOverride implements UserPermissionOverrideInterface
{
    #[Column(type: 'boolean')]
    private bool $allow;

    #[Id]
    #[ManyToOne(targetEntity: User::class, inversedBy: 'userPermissionOverride')]
    #[JoinColumn(name: 'idUser', referencedColumnName: 'idUser', nullable: false)]
    private User $user;

    #[Id]
    #[ManyToOne(targetEntity: UserPermission::class)]
    #[JoinColumn(name: 'idUserPermission', referencedColumnName: 'idUserPermission', nullable: false)]
    private UserPermission $userPermission;


    // Getters
    public function isAllow(): bool
    {
        return $this->allow;
    }
    public function getUser(): User
    {
        return $this->user;
    }
    public function getUserPermission(): UserPermission
    {
        return $this->userPermission;
    }

    // Setters
    public function setAllow(bool $allow): self
    {
        $this->allow = $allow;
        return $this;
    }
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
    public function setUserPermission(UserPermission $userPermission): self
    {
        $this->userPermission = $userPermission;
        return $this;
    }
}