<?php

declare(strict_types=1);

namespace JR\Tracker\Fixture;

use Doctrine\Common\DataFixtures\Loader;
use JR\Tracker\Fixture\Fixtures\UserRoleTypeFixture;
use JR\Tracker\Fixture\Fixtures\UserPermissionFixture;
use JR\Tracker\Fixture\Fixtures\UserRolePermissionFixture;

$loader = new Loader();
$loader->addFixture(new UserRoleTypeFixture());
$loader->addFixture(new UserPermissionFixture());
$loader->addFixture(new UserRolePermissionFixture());


return $loader;