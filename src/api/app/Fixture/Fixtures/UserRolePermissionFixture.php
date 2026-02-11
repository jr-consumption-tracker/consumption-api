<?php

declare(strict_types=1);

namespace JR\Tracker\Fixture\Fixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use JR\Tracker\Entity\User\Implementation\UserRoleType;
use JR\Tracker\Entity\User\Implementation\UserPermission;
use JR\Tracker\Entity\User\Implementation\UserRolePermission;

class UserRolePermissionFixture implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $rolePermissionMap = [
            'OWNER' => [
                'USER_VIEW',
                'USER_EDIT',
                'USER_DELETE',
                'ROLE_ASSIGN',
                'PERMISSION_EDIT',

                'CONSUMPTION_VIEW',
                'CONSUMPTION_EDIT',
                'CONSUMPTION_DELETE',

                'METER_VIEW',
                'METER_EDIT',
                'METER_DELETE',

                'PRICE_VIEW',
                'PRICE_EDIT',
                'PRICE_DELETE',

                'INVOICE_VIEW',
                'INVOICE_EDIT',
                'INVOICE_DELETE',

                'TICKET_VIEW',
                'TICKET_MANAGE',

                'LOG_VIEW',
                'SETTINGS_EDIT',
            ],        
            // SUPER_ADMIN = vše, plná kontrola nad systémem
            'SUPER_ADMIN' => [
                // uživatelé
                'USER_VIEW',
                'USER_EDIT',
                'USER_DELETE',
                'ROLE_ASSIGN',
                'PERMISSION_EDIT',

                // odběrná místa a měření
                'CONSUMPTION_VIEW',
                'CONSUMPTION_EDIT',
                'CONSUMPTION_DELETE',

                'METER_VIEW',
                'METER_EDIT',
                'METER_DELETE',

                // ceny
                'PRICE_VIEW',
                'PRICE_EDIT',
                'PRICE_DELETE',

                // faktury / vyúčtování
                'INVOICE_VIEW',
                'INVOICE_EDIT',
                'INVOICE_DELETE',

                // podpora
                'TICKET_VIEW',
                'TICKET_MANAGE',

                // logy a nastavení
                'LOG_VIEW',
                'SETTINGS_EDIT',
            ],

            // ADMIN = správa systému bez úplného plného přístupu
            'ADMIN' => [
                'USER_VIEW',
                'USER_EDIT',

                'CONSUMPTION_VIEW',
                'CONSUMPTION_EDIT',

                'METER_VIEW',
                'METER_EDIT',

                'PRICE_VIEW',
                'PRICE_EDIT',

                'INVOICE_VIEW',
                'INVOICE_EDIT',

                'TICKET_VIEW',
                'TICKET_MANAGE',

                'LOG_VIEW',
            ],

            // MODERATOR = dohled nad daty, schvalování nebo kontrola
            'MODERATOR' => [
                'CONSUMPTION_VIEW',
                'CONSUMPTION_APPROVE',
                'INVOICE_VIEW',

                'METER_VIEW',

                'TICKET_VIEW',
            ],

            // SUPPORT = zákaznická podpora
            'SUPPORT' => [
                'TICKET_VIEW',
                'TICKET_MANAGE',

                'CONSUMPTION_VIEW',
                'INVOICE_VIEW',

                'METER_VIEW',
            ],

            // EDITOR = správa číselníků a interní obsah
            'EDITOR' => [
                'ENERGY_TYPE_VIEW',
                'ENERGY_TYPE_EDIT',
                'ENERGY_TYPE_DELETE',

                'ENERGY_VARIANT_VIEW',
                'ENERGY_VARIANT_EDIT',
                'ENERGY_VARIANT_DELETE',

                'UNIT_VIEW',
                'UNIT_EDIT',
                'UNIT_DELETE',

                'ARTICLE_PUBLISH' => 'Publikování interních článků/návodů',
                'ARTICLE_EDIT',
                'ARTICLE_DELETE',

                'FAQ_EDIT',
            ],

            // AUDITOR = jen náhled
            'AUDITOR' => [
                'USER_VIEW',
                'CONSUMPTION_VIEW',
                'INVOICE_VIEW',
                'LOG_VIEW',
            ],
        ];

        // Načtení všech rolí a oprávnění z DB
        $userRoleTypes = $manager->getRepository(UserRoleType::class)->findAll();
        $userPermissions = $manager->getRepository(UserPermission::class)->findAll();

        // Pomocné funkce pro hledání podle kódu
        $getRole = function ($code) use ($userRoleTypes) {
            foreach ($userRoleTypes as $role) {
                if ($role->getCode() === $code) {
                    return $role;
                }
            }
            return null;
        };
        $getPermission = function ($code) use ($userPermissions) {
            foreach ($userPermissions as $permission) {
                if ($permission->getCode() === $code) {
                    return $permission;
                }
            }
            return null;
        };

        foreach ($rolePermissionMap as $roleCode => $permissionCodes) {
            $role = $getRole($roleCode);

            if (!$role) {
                echo "Role not found: $roleCode\n";
                continue;
            }
            foreach ($permissionCodes as $permissionCode) {
                $permission = $getPermission($permissionCode);
                if (!$permission) {
                    echo "Permission not found: $permissionCode\n";
                    continue;
                }

                $rolePermission = new UserRolePermission();
                $rolePermission
                    ->setUserRoleType($role)
                    ->setUserPermission($permission);

                $manager->persist($rolePermission);

            }
            $manager->persist($role);
        }

        $manager->flush();
        $manager->clear();
    }
}