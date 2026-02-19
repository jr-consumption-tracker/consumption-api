<?php

declare(strict_types=1);

namespace JR\Tracker\Fixture\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use JR\Tracker\Entity\User\Implementation\UserRoleType;

class UserRoleTypeFixture implements FixtureInterface
{
  public function load(ObjectManager $manager): void
  {
    $roles = [
      ['code' => 'AUDITOR', 'value' => 7625, 'description' => 'Auditor'],
      ['code' => 'SUPPORT', 'value' => 4468, 'description' => 'Podpora'],
      ['code' => 'EDITOR', 'value' => 1984, 'description' => 'Editor'],
      ['code' => 'MODERATOR', 'value' => 3128, 'description' => 'Moderátor'],
      ['code' => 'ADMIN', 'value' => 5150, 'description' => 'Administrátor'],
      ['code' => 'SUPER_ADMIN', 'value' => 2821, 'description' => 'Super administrátor'],
    ];

    foreach ($roles as $item) {
      $role = new UserRoleType();
      $role
        ->setCode($item['code'])
        ->setValue($item['value'])
        ->setDescription($item['description']);

      $manager->persist($role);
    }

    $manager->flush();
    $manager->clear();
  }
}
