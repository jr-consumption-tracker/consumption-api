<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Contract;

use JR\Tracker\Entity\User\Implementation\User;
use JR\Tracker\Entity\User\Implementation\UserRoleType;

interface UserRoleInterface
{
  // Getters
  public function getUserRoleType(): UserRoleTypeInterface;

  // Setters
  public function setUser(User $user): self;

  public function setUserRoleType(UserRoleType $userRoleType): self;
}
