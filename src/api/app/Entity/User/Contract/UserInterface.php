<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Contract;

interface UserInterface
{
  // Getters
  public function getUuid(): string;

  public function getEmail(): string;

  public function getEmailVerifiedAt(): ?\DateTimeImmutable;

  public function getPassword(): string;

  public function getIsDisabled(): ?bool;

  public function getWebLoginRestrictedUntil(): ?\DateTime;

  public function getAdminLoginRestrictedUntil(): ?\DateTime;

  // Setters
  public function setUuid(): self;

  public function setEmail(string $email): self;

  public function setEmailVerifiedAt(): self;

  public function setPassword(string $password): self;
}
