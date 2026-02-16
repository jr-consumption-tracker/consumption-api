<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

interface VerifyEmailServiceInterface
{
    public function attemptVerifyEmail(string $token): void;
    public function createEmailVerificationLink(string $email, int $expiresHours): ?string;
}