<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

use JR\Tracker\Entity\User\Contract\UserInterface;

interface VerifyEmailServiceInterface
{
    public function attemptVerify(string $token): void;
    public function attemptResend(string $email): void;
    public function createLink(UserInterface $user, int $expiresHours): string;
}