<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

interface VerifyEmailServiceInterface
{
    public function attemptVerify(string $token): void;
    public function attemptResend(string $email): void;
}