<?php

declare(strict_types=1);

namespace JR\Tracker\DataObject\Data;

class RegisterUserData
{
  public function __construct(
    public readonly string $email,
    public readonly string $password
  ) {
  }

  public function withHashedPassword(string $hashedPassword): self
  {
    return new self(
      $this->email,
      $hashedPassword,
    );
  }
}
