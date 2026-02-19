<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Contract;

interface UserPasswordResetInterface
{
  // Getters
  public function getId(): int;

  public function getEmail(): string;

  public function getToken(): string;

  public function getIsExpired(): bool;

  // Setters
  public function setToken(): self;

  public function setExpiresAt(int $hours): self;

  public function setCreatedAt(): self;
}
