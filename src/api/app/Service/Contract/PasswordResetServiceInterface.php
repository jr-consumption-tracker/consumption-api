<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

interface PasswordResetServiceInterface
{
  public function attemptResetPassword(string $email): void;
}