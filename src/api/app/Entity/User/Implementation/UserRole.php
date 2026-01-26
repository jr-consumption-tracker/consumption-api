<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use JR\Tracker\Entity\User\Contract\UserRoleInterface;

#[Entity]
#[Table(name: 'userRole')]
class UserRole implements UserRoleInterface
{
    #[Id]
    #[ManyToOne(targetEntity: User::class, inversedBy: 'userRole')]
    #[JoinColumn(name: 'idUser', referencedColumnName: 'idUser', nullable: false)]
    private User $user;

    #[Id]
    #[ManyToOne(targetEntity: UserRoleType::class, inversedBy: 'userRole')]
    #[JoinColumn(name: 'idUserRoleType', referencedColumnName: 'idUserRoleType', nullable: false)]
    private UserRoleType $userRoleType;
}