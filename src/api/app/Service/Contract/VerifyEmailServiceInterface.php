<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

interface VerifyEmailServiceInterface
{
    public function attemptVerify(string $token): void;
    public function createVerificationLink(string $email, int $expiresHours): ?string;
    public function attemptResend(string $email): void;
}