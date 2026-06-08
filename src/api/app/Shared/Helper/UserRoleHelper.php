<?php

declare(strict_types=1);

namespace JR\Tracker\Shared\Helper;

use JR\Tracker\Enum\UserRoleTypeEnum;

class UserRoleHelper
{
  public static function getRoleValueArrayFromUserRoles(array $userRoles): array
  {
    return array_map(
      fn($role) => $role->getValue(),
      $userRoles
    );
  }

  public static function hasRole(array $userRoles, UserRoleTypeEnum $userRole): bool
  {
    return !empty(array_filter(
      $userRoles,
      fn($role) => $role->getValue() === $userRole->value
    ));
  }
}
