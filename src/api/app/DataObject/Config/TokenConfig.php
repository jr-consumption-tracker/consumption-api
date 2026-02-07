<?php

declare(strict_types=1);

namespace JR\Tracker\DataObject\Config;

use DateTime;

class TokenConfig
{
    public function __construct(
        public readonly int $expAccess,
        public readonly int $expRefresh,
        public readonly string $algorithm,
        public readonly string $keyAccess,
        public readonly string $keyRefresh

    ) {
    }
}