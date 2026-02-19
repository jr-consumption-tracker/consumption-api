<?php

declare(strict_types=1);

namespace JR\Tracker\DataObject\Config;

use JR\Tracker\Enum\SameSiteEnum;

class SessionConfig
{
  public function __construct(
    public readonly string $name,
    public readonly string $flashName,
    public readonly bool $secure,
    public readonly bool $httpOnly,
    public readonly SameSiteEnum $sameSite
  ) {
  }
}
