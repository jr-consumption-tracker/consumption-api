<?php

declare(strict_types=1);

namespace JR\Tracker\DataObject\Data;

class LoginUserData
{
  public function __construct(
    public readonly string $email,
    public readonly string $password,
    public readonly bool $persistLogin,
  ) {
  }
}
