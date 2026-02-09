<?php

declare(strict_types=1);

namespace JR\Tracker\Shared\Helper;

use JR\Tracker\Enum\UserRoleTypeEnum;
use JR\Tracker\Entity\User\Contract\UserRoleInterface;

class UserRoleHelper
{

    public static function getRoleValueArrayFromUserRoles(array $userRoles)
    {
        $getRoles = self::getRoles();

        return array_map(
            fn($role) => $role->getUserRoleType()->getValue(),
            $userRoles
        );

        return array_map($getRoles, $userRoles);
    }

    public static function hasRole(array $userRoles, UserRoleTypeEnum $userRole): bool
    {
        return !empty(array_filter(
            $userRoles,
            fn($role) => $role->getUserRoleType()->getValue() === $userRole->value
        ));
    }

    #region Private methods
    private static function getRoles()
    {
        return function (UserRoleInterface $userRole) {
            return $userRole->getUserRoleType()->getValue();
        };
    }
    #endregion
}