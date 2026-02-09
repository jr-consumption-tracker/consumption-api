<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\DataObject\Data\LoginUserData;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;

interface AuthServiceInterface
{
    public function register(RegisterUserData $data): UserInterface;

    /**
     * Attempt to login user
     * @param string[] $credentials
     * @return \JR\Tracker\Enum\AuthAttemptStatusEnum|array
     * @author Jan Ribka
     */
    public function attemptLogin(LoginUserData $data, DomainContextEnum $domain): array;

    // public function attemptLogout(): LogoutAttemptStatusEnum;

    // public function attemptRefreshToken(array $credentials): RefreshTokenAttemptStatusEnum|array;
}