<?php

declare(strict_types=1);

namespace JR\Tracker\Repository\Contract;

use JR\Tracker\Entity\User\Contract\UserPasswordResetInterface;

interface PasswordResetRepositoryInterface
{
    public function getActiveToken(string $email): ?UserPasswordResetInterface;
    public function update(UserPasswordResetInterface $passwordReset): void;
    public function create(UserPasswordResetInterface $passwordReset): void;
}
