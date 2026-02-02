<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Contract;

interface UserInterface
{
    // Getters
    public function getUuid(): string;
    public function getEmail(): string;
    public function getPassword(): string;

    // Setters
    public function setUuid(): self;
    public function setEmail(string $email): self;
    public function setPassword(string $password): self;
}