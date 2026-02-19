<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JR\Tracker\Entity\User\Contract\UserRoleInterface;
use JR\Tracker\Entity\User\Contract\UserRoleTypeInterface;

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

  // Getters
  public function getUser(): User
  {
    return $this->user;
  }

  public function getUserRoleType(): UserRoleTypeInterface
  {
    return $this->userRoleType;
  }

  // Setters
  public function setUser(User $user): self
  {
    $this->user = $user;

    return $this;
  }

  public function setUserRoleType(UserRoleType $userRoleType): self
  {
    $this->userRoleType = $userRoleType;

    return $this;
  }
}
