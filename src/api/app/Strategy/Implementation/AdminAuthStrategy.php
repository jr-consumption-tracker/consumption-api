<?php

declare(strict_types=1);

namespace JR\Tracker\Strategy\Implementation;

use JR\Tracker\DataObject\Config\AdminAuthCookieConfig;
use JR\Tracker\DataObject\Config\AdminTokenConfig;
use JR\Tracker\DataObject\Config\AuthCookieConfig;
use JR\Tracker\DataObject\Config\TokenConfig;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Enum\UserRoleTypeEnum;
use JR\Tracker\Exception\ValidationException;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Shared\Helper\UserRoleHelper;
use JR\Tracker\Strategy\Contract\AuthStrategyInterface;

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

  public function getCookieConfig(?bool $persistLogin = false, ?\DateTime $fixedExpiresAt = null): AuthCookieConfig
  {
    // For admin, we might always want session cookie, or respect persistLogin
    $expires = $persistLogin
      ? $this->tokenConfig->expRefresh
      : ($fixedExpiresAt?->getTimestamp() ?? $this->tokenConfig->expRefreshSession);

    return new AuthCookieConfig(
      $this->authCookieConfig->name,
      $this->authCookieConfig->secure,
      $this->authCookieConfig->httpOnly,
      $this->authCookieConfig->sameSite,
      $expires,
      $this->authCookieConfig->path
    );
  }

  public function verifyUser(?UserInterface $user, string $password): void
  {
    if (!isset($user)) {
      throw new ValidationException(['general' => ['invalidCredentials']], HttpStatusCode::UNAUTHORIZED->value);
    }

    if ($user->getAdminLoginRestrictedUntil() && $user->getAdminLoginRestrictedUntil() > new \DateTime()) {
      throw new ValidationException(['general' => ['tooManyRequests']], HttpStatusCode::TOO_MANY_REQUESTS->value);
    }

    if ($user->getIsDisabled()) {
      throw new ValidationException(['general' => ['accessDenied']], HttpStatusCode::LOCKED->value);
    }

    if (!password_verify($password, $user->getPassword())) {
      $this->userRepository->logLoginAttempt(DomainContextEnum::ADMIN, $user, false);
      $this->userRepository->checkAndRestrictLogin(DomainContextEnum::ADMIN, $user);

      throw new ValidationException(['general' => ['invalidCredentials']], HttpStatusCode::UNAUTHORIZED->value);
    }

    $emailVerifiedAt = $user->getEmailVerifiedAt();
    if (!isset($emailVerifiedAt)) {
      throw new ValidationException(['action' => ['emailNotVerified']], HttpStatusCode::UNPROCESSABLE_ENTITY->value);
    }

    $userRoles = $this->userRepository->getRoleByIdUser($user->getUuid());

    if (!UserRoleHelper::hasRole($userRoles, UserRoleTypeEnum::ADMIN)) {
      $this->userRepository->logLoginAttempt(DomainContextEnum::ADMIN, $user, false);
      $this->userRepository->checkAndRestrictLogin(DomainContextEnum::ADMIN, $user);

      throw new ValidationException(['general' => ['accessDenied']], HttpStatusCode::UNPROCESSABLE_ENTITY->value);
    }
  }
}
