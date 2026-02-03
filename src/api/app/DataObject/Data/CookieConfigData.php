<?php

declare(strict_types=1);

namespace JR\Tracker\DataObject\Data;

use JR\Tracker\Enum\SameSiteEnum;

class CookieConfigData
{
    public function __construct(
        public readonly bool $secure,
        public readonly bool $httpOnly,
        public readonly SameSiteEnum $sameSite,
        public readonly int|string $expires,
        public readonly string $path,
    ) {
    }
}