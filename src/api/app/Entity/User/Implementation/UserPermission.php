<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Implementation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use JR\Tracker\Entity\User\Contract\UserPermissionInterface;

#[Entity]
#[Table(name: 'userPermission')]
class UserPermission implements UserPermissionInterface
{
  #[Id]
  #[GeneratedValue(strategy: 'AUTO')]
  #[Column]
  /** @phpstan-ignore-next-line */
  private int $idUserPermission;

  #[Column(length: 25, unique: true)]
  private string $code;

  #[Column(length: 100)]
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

  // Getters
  public function getIdUserPermission(): int
  {
    return $this->idUserPermission;
  }

  public function getCode(): string
  {
    return $this->code;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function getValue(): int
  {
    return $this->value;
  }

  public function getUserRolePermission(): Collection
  {
    return $this->userRolePermission;
  }

  public function getUserPermissionOverride(): Collection
  {
    return $this->userPermissionOverride;
  }

  // Setters
  public function setCode(string $code): self
  {
    $this->code = $code;

    return $this;
  }

  public function setDescription(string $description): self
  {
    $this->description = $description;

    return $this;
  }

  public function setValue(int $value): self
  {
    $this->value = $value;

    return $this;
  }
}
