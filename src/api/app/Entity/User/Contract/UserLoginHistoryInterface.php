<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Contract;

interface UserLoginHistoryInterface
{
  // Setters
  public function setLoginAttemptAt(\DateTimeImmutable $loginAttemptAt): self;
  public function setIsSuccessful(bool $isSuccessful): self;
  public function setUser(UserInterface $user): self;
}