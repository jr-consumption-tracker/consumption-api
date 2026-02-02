<?php

declare(strict_types=1);

namespace JR\Tracker\Repository\Contract;

use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;

interface UserRepositoryInterface
{
    public function createUser(RegisterUserData $data): UserInterface;
}

