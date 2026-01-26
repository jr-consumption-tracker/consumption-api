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
use JR\Tracker\Entity\User\Contract\UserRoleTypeInterface;

#[Entity]
#[Table(name: 'userRoleType')]
class UserRoleType implements UserRoleTypeInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column]
    private int $idUserRoleType;

    #[Column(length: 25, unique: true)]
    private string $code;

    #[Column(type: "smallint", unique: true)]
    private int $value;

    #[Column(length: 50)]
    private string $description;

    #[OneToMany(mappedBy: 'userRleType', targetEntity: UserRole::class)]
    private Collection $userRole;

    #[OneToMany(mappedBy: 'userRoleType', targetEntity: UserRolePermission::class)]
    private Collection $userRolePermission;

    public function __construct()
    {
        $this->userRole = new ArrayCollection();
        $this->userRolePermission = new ArrayCollection();
    }
}