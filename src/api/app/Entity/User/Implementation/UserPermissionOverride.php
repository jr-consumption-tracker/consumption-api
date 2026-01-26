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
}