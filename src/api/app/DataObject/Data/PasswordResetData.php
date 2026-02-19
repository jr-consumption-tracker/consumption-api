<?php

declare(strict_types=1);

namespace JR\Tracker\DataObject\Data;

class PasswordResetData
{
  public function __construct(
    public readonly string $password,
    public readonly string $confirmPassword,
    public readonly string $token,
  ) {
  }
}
