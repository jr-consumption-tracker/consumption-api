<?php

declare(strict_types=1);

namespace JR\Tracker\Strategy\Implementation;

use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Enum\UserRoleTypeEnum;
use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\Shared\Helper\UserRoleHelper;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\DataObject\Config\TokenConfig;
use JR\Tracker\DataObject\Config\AdminTokenConfig;
use JR\Tracker\DataObject\Config\AuthCookieConfig;
use JR\Tracker\DataObject\Config\AdminAuthCookieConfig;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Strategy\Contract\AuthStrategyInterface;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;

class AdminAuthStrategy implements AuthStrategyInterface
{
    public function __construct(
        private readonly AdminTokenConfig $tokenConfig,
        private readonly AdminAuthCookieConfig $authCookieConfig,
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function getTokenConfig(): TokenConfig
    {
        return $this->tokenConfig;
    }

    public function getCookieConfig(?bool $persistLogin = false): AuthCookieConfig
    {
        // For admin, we might always want session cookie, or respect persistLogin
        return new AuthCookieConfig(
            $this->authCookieConfig->name,
            $this->authCookieConfig->secure,
            $this->authCookieConfig->httpOnly,
            $this->authCookieConfig->sameSite,
            $persistLogin ? $this->authCookieConfig->expires : 0,
            $this->authCookieConfig->path
        );
    }

    public function verifyUser(?UserInterface $user, string $password): void
    {
        if (!isset($user)) {
            throw new ValidationException(['unauthorized' => ['incorrectLoginPassword']], HttpStatusCode::UNAUTHORIZED->value);
        }

        if ($user->getAdminLoginRestrictedUntil() && $user->getAdminLoginRestrictedUntil() > new \DateTime()) {
            throw new ValidationException(['forbidden' => ['loginRestricted']], HttpStatusCode::FORBIDDEN->value);
        }

        if ($user->getIsDisabled()) {
            throw new ValidationException(['forbidden' => ['accessDenied']], HttpStatusCode::FORBIDDEN->value);
        }

        if (!password_verify($password, $user->getPassword())) {
            $this->userRepository->logLoginAttempt(DomainContextEnum::ADMIN, $user, false);

            throw new ValidationException(['unauthorized' => ['incorrectLoginPassword']], HttpStatusCode::UNAUTHORIZED->value);
        }

        $emailVerifiedAt = $user->getEmailVerifiedAt();
        if (!isset($emailVerifiedAt)) {
            throw new ValidationException(['forbidden' => ['emailNotVerified']], HttpStatusCode::FORBIDDEN->value);
        }

        $userRoles = $this->userRepository->getRoleByIdUser($user->getUuid());

        if (!UserRoleHelper::hasRole($userRoles, UserRoleTypeEnum::ADMIN)) {
            $this->userRepository->logLoginAttempt(DomainContextEnum::ADMIN, $user, false);

            throw new ValidationException(['forbidden' => ['accessDenied']], HttpStatusCode::FORBIDDEN->value);
        }
    }
}
