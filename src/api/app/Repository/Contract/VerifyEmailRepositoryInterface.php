<?php

declare(strict_types=1);

namespace JR\Tracker\Repository\Contract;

use JR\Tracker\Entity\User\Contract\UserVerifyEmailInterface;

interface VerifyEmailRepositoryInterface
{
    public function getActiveTokenByEmail(string $email): ?UserVerifyEmailInterface;
    public function updateVerifyEmail(UserVerifyEmailInterface $userVerifyEmil): void;
    public function createVerifyEmail(UserVerifyEmailInterface $userVerifyEmil): void;
    public function deleteExpiredTokens(): int;
}
