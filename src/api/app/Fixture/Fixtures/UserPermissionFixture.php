<?php

declare(strict_types=1);

namespace JR\Tracker\Fixture\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use JR\Tracker\Entity\User\Implementation\UserPermission;

class UserPermissionFixture implements FixtureInterface
{
  public function load(ObjectManager $manager): void
  {
    $permissions = [
      // uživatelé
      ['code' => 'USER_VIEW', 'value' => 1, 'description' => 'Zobrazit uživatele'],
      ['code' => 'USER_EDIT', 'value' => 2, 'description' => 'Upravit uživatele'],
      ['code' => 'USER_DELETE', 'value' => 3, 'description' => 'Smazat uživatele'],
      ['code' => 'ROLE_ASSIGN', 'value' => 10, 'description' => 'Přiřazovat role uživatelům'],
      ['code' => 'PERMISSION_EDIT', 'value' => 20, 'description' => 'Spravovat oprávnění rolí'],

      // logy a nastavení
      ['code' => 'LOG_VIEW', 'value' => 30, 'description' => 'Prohlížet systémové logy'],
      ['code' => 'SETTINGS_EDIT', 'value' => 100, 'description' => 'Spravovat nastavení systému'],

      // odběrná místa a měření
      ['code' => 'CONSUMPTION_VIEW', 'value' => 40, 'description' => 'Zobrazit spotřeby a odběrná místa'],
      ['code' => 'CONSUMPTION_EDIT', 'value' => 41, 'description' => 'Upravit spotřeby a odběrná místa'],
      ['code' => 'CONSUMPTION_DELETE', 'value' => 42, 'description' => 'Smazat spotřeby nebo odběrná místa'],
      ['code' => 'CONSUMPTION_APPROVE', 'value' => 43, 'description' => 'Schválit nebo kontrolovat spotřeby'],

      // měřidla
      ['code' => 'METER_VIEW', 'value' => 50, 'description' => 'Zobrazit měřidla'],
      ['code' => 'METER_EDIT', 'value' => 51, 'description' => 'Upravit měřidla'],
      ['code' => 'METER_DELETE', 'value' => 52, 'description' => 'Smazat měřidla'],

      // ceny a tarif
      ['code' => 'PRICE_VIEW', 'value' => 60, 'description' => 'Zobrazit ceny energií'],
      ['code' => 'PRICE_EDIT', 'value' => 61, 'description' => 'Upravit ceny energií'],
      ['code' => 'PRICE_DELETE', 'value' => 62, 'description' => 'Smazat ceny energií'],

      // faktury a vyúčtování
      ['code' => 'INVOICE_VIEW', 'value' => 70, 'description' => 'Zobrazit faktury a vyúčtování'],
      ['code' => 'INVOICE_EDIT', 'value' => 71, 'description' => 'Upravit faktury a vyúčtování'],
      ['code' => 'INVOICE_DELETE', 'value' => 72, 'description' => 'Smazat faktury a vyúčtování'],

      // podpora / tikety
      ['code' => 'TICKET_VIEW', 'value' => 90, 'description' => 'Prohlížet tikety/support požadavky'],
      ['code' => 'TICKET_MANAGE', 'value' => 91, 'description' => 'Spravovat tikety/support požadavky'],

      // číselníky a interní obsah
      ['code' => 'ENERGY_TYPE_VIEW', 'value' => 110, 'description' => 'Zobrazit typy energií'],
      ['code' => 'ENERGY_TYPE_EDIT', 'value' => 111, 'description' => 'Upravit typy energií'],
      ['code' => 'ENERGY_TYPE_DELETE', 'value' => 112, 'description' => 'Smazat typy energií'],

      ['code' => 'ENERGY_VARIANT_VIEW', 'value' => 120, 'description' => 'Zobrazit varianty energií (VT/NT, teplá/studená voda)'],
      ['code' => 'ENERGY_VARIANT_EDIT', 'value' => 121, 'description' => 'Upravit varianty energií'],
      ['code' => 'ENERGY_VARIANT_DELETE', 'value' => 122, 'description' => 'Smazat varianty energií'],

      ['code' => 'UNIT_VIEW', 'value' => 130, 'description' => 'Zobrazit jednotky měření'],
      ['code' => 'UNIT_EDIT', 'value' => 131, 'description' => 'Upravit jednotky měření'],
      ['code' => 'UNIT_DELETE', 'value' => 132, 'description' => 'Smazat jednotky měření'],
    ];

    foreach ($permissions as $permData) {
      $permission = new UserPermission();
      $permission->setCode($permData['code']);
      $permission->setDescription($permData['description']);
      $permission->setValue($permData['value']);
      $manager->persist($permission);
    }

    $manager->flush();
    $manager->clear();
  }
}
