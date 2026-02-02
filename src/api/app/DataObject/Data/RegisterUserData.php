<?php

declare(strict_types=1);

namespace JR\Tracker\DataObject\Data;

class RegisterUserData
{
    public function __construct(
        public string $email,
        public string $hashedPassword
    ) {
    }
}