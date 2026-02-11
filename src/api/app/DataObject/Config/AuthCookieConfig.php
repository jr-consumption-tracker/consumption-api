<?php

declare(strict_types=1);

namespace JR\Tracker\DataObject\Config;

use JR\Tracker\Enum\SameSiteEnum;

class AuthCookieConfig
{
    public function __construct(
        public readonly string $name,
        public readonly bool $secure,
        public readonly bool $httpOnly,
        public readonly SameSiteEnum $sameSite,
        public readonly int $expires,
        public readonly string $path,
    ) {
    }

    public static function fromAuthCookieConfig(AuthCookieConfig $other): self
    {
        return new self(
            $other->name,
            $other->secure,
            $other->httpOnly,
            $other->sameSite,
            $other->expires,
            $other->path
        );
    }
    
}