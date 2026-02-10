<?php

declare(strict_types=1);

namespace JR\Tracker\Repository\Contract;

use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Contract\UserTokenInterface;

interface UserRepositoryInterface
{
    public function create(RegisterUserData $data): UserInterface;
    public function getByEmail(string $email): ?UserInterface;
    public function logLoginAttempt(DomainContextEnum $domain, UserInterface $user, bool $successful): void;
    public function getRoleByIdUser(string $idUser): array;
    public function refreshTokenExists(string $refreshToken): bool;
    public function deleteRefreshTokes(string $idUser): void;
    public function createRefreshToken(UserInterface $user, string $refreshToken, DomainContextEnum $domain): void;
    public function getByRefreshToken(string $refreshToken, DomainContextEnum $domain): ?UserInterface;
    public function getRefreshToken(string $idUser, DomainContextEnum $domain): UserTokenInterface|null;
    public function deleteRefreshToken(string $idUser, DomainContextEnum $domain): void;
}

