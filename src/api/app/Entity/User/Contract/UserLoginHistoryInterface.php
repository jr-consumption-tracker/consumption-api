<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Contract;

use JR\Tracker\Entity\User\Implementation\User;

interface UserLoginHistoryInterface
{
  // Setters
  public function setLoginAttemptAt(\DateTimeImmutable $loginAttemptAt): self;

  public function setIsSuccessful(bool $isSuccessful): self;

  public function setUser(User $user): self;
}
