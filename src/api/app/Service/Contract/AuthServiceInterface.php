<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

use JR\Tracker\Enum\AuthAttemptStatusEnum;
use JR\Tracker\Enum\LogoutAttemptStatusEnum;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Enum\RefreshTokenAttemptStatusEnum;

interface AuthServiceInterface
{
    public function registerUser(RegisterUserData $data): UserInterface;

    /**
     * Attempt to login user
     * @param string[] $credentials
     * @return \JR\Tracker\Enum\AuthAttemptStatusEnum|array
     * @author Jan Ribka
     */
    // public function attemptLogin(array $credentials): AuthAttemptStatusEnum|array;

    // public function attemptTwoFactorLogin(array $data): bool;

    // public function attemptLogout(): LogoutAttemptStatusEnum;

    // public function attemptRefreshToken(array $credentials): RefreshTokenAttemptStatusEnum|array;
}