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
use JR\Tracker\Entity\User\Contract\UserRoleTypeInterface;

#[Entity]
#[Table(name: 'userRoleType')]
class UserRoleType implements UserRoleTypeInterface
{
  #[Id]
  #[GeneratedValue(strategy: 'AUTO')]
  #[Column]
  /** @phpstan-ignore-next-line */
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

  // Getters
  public function getIdUserRoleType(): int
  {
    return $this->idUserRoleType;
  }

  public function getCode(): string
  {
    return $this->code;
  }

  public function getValue(): int
  {
    return $this->value;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function getUserRole(): Collection
  {
    return $this->userRole;
  }

  public function getUserRolePermission(): Collection
  {
    return $this->userRolePermission;
  }

  // Setters
  public function setCode(string $code): self
  {
    $this->code = $code;

    return $this;
  }

  public function setValue(int $value): self
  {
    $this->value = $value;

    return $this;
  }

  public function setDescription(string $description): self
  {
    $this->description = $description;

    return $this;
  }

  public function setUserRolePermission(Collection $userRolePermission): self
  {
    $this->userRolePermission = $userRolePermission;

    return $this;
  }
}
