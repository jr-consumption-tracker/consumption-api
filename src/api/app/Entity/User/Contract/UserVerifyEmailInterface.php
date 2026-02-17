<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Contract;

interface UserVerifyEmailInterface
{
    // Getters    
    public function getIdVerifyEmail(): int;
    public function getToken(): string;

    // Setters       
    public function setToken(): self;
    public function setExpiresAt(int $hours): self;
    public function setCreatedAt(): self;
}