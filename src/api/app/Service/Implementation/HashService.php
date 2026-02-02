<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\Service\Contract\HashServiceInterface;

class HashService implements HashServiceInterface
{
    public function hash(string $input): string
    {
        return password_hash($input, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}