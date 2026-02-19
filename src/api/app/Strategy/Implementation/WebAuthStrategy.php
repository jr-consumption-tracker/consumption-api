<?php

declare(strict_types=1);

namespace JR\Tracker\Strategy\Implementation;

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

class WebAuthStrategy implements AuthStrategyInterface
{
  public function __construct(
    private readonly TokenConfig $tokenConfig,
    private readonly AuthCookieConfig $authCookieConfig,
    private readonly UserRepositoryInterface $userRepository,
  ) {
  }

  public function getTokenConfig(): TokenConfig
  {
    return $this->tokenConfig;
  }

  public function getCookieConfig(?bool $persistLogin = false): AuthCookieConfig
  {
    return new AuthCookieConfig(
      $this->authCookieConfig->name,
      $this->authCookieConfig->secure,
      $this->authCookieConfig->httpOnly,
      $this->authCookieConfig->sameSite,
      $persistLogin ? $this->authCookieConfig->expires : 0, // 0 = session
      $this->authCookieConfig->path
    );
  }

  public function verifyUser(?UserInterface $user, string $password): void
  {
    if (!isset($user)) {
      throw new ValidationException(['unauthorized' => ['incorrectLoginPassword']], HttpStatusCode::UNAUTHORIZED->value);
    }

    if ($user->getWebLoginRestrictedUntil() && $user->getWebLoginRestrictedUntil() > new \DateTime()) {
      throw new ValidationException(['forbidden' => ['loginRestricted']], HttpStatusCode::FORBIDDEN->value);
    }

    if ($user->getIsDisabled()) {
      throw new ValidationException(['forbidden' => ['accessDenied']], HttpStatusCode::FORBIDDEN->value);
    }

    if (!password_verify($password, $user->getPassword())) {
      $this->userRepository->logLoginAttempt(DomainContextEnum::WEB, $user, false);

      throw new ValidationException(['unauthorized' => ['incorrectLoginPassword']], HttpStatusCode::UNAUTHORIZED->value);
    }

    $emailVerifiedAt = $user->getEmailVerifiedAt();
    if (!isset($emailVerifiedAt)) {
      throw new ValidationException(['forbidden' => ['emailNotVerified']], HttpStatusCode::FORBIDDEN->value);
    }

    $userRoles = $this->userRepository->getRoleByIdUser($user->getUuid());

    if (!UserRoleHelper::hasRole($userRoles, UserRoleTypeEnum::EDITOR)) {
      $this->userRepository->logLoginAttempt(DomainContextEnum::WEB, $user, false);

      throw new ValidationException(['forbidden' => ['accessDenied']], HttpStatusCode::FORBIDDEN->value);
    }
  }
}
