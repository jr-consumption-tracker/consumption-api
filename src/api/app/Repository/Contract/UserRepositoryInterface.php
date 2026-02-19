<?php

declare(strict_types=1);

namespace JR\Tracker\Repository\Contract;

use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Contract\UserTokenInterface;
use JR\Tracker\Entity\User\Implementation\UserVerifyEmail;
use JR\Tracker\Enum\DomainContextEnum;

interface UserRepositoryInterface
{
  public function create(RegisterUserData $data): UserInterface;

  public function update(UserInterface $user): void;

  public function getByEmail(string $email): ?UserInterface;

  public function logLoginAttempt(DomainContextEnum $domain, UserInterface $user, bool $successful): void;

  public function getRoleByIdUser(string $idUser): array;

  public function refreshTokenExists(string $refreshToken): bool;

  public function deleteRefreshTokes(string $idUser, DomainContextEnum $domain): void;

  public function createRefreshToken(UserInterface $user, string $refreshToken, DomainContextEnum $domain, \DateTime $expiresAt): void;

  public function getByRefreshToken(string $refreshToken, DomainContextEnum $domain): ?UserInterface;

  public function getRefreshToken(string $idUser, DomainContextEnum $domain): UserTokenInterface|null;

  public function updateRefreshToken(string $oldToken, string $newToken, \DateTime $expiresAt): void;

  public function deleteRefreshToken(string $idUser, DomainContextEnum $domain): void;

  public function getVerificationToken(string $token): ?UserVerifyEmail;

  public function deleteVerificationToken(string $token): void;
}
