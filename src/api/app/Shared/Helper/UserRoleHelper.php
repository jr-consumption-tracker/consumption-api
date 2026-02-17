<?php

declare(strict_types=1);

namespace JR\Tracker\Shared\Helper;

use JR\Tracker\Enum\UserRoleTypeEnum;

class UserRoleHelper
{

    public static function getRoleValueArrayFromUserRoles(array $userRoles)
    {
        return array_map(
            fn($role) => $role->getUserRoleType()->getValue(),
            $userRoles
        );
    }

    public static function hasRole(array $userRoles, UserRoleTypeEnum $userRole): bool
    {
        return !empty(array_filter(
            $userRoles,
            fn($role) => $role->getUserRoleType()->getValue() === $userRole->value
        ));
    }
}