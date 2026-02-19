<?php

declare(strict_types=1);

namespace JR\Tracker\DataObject\Data;

use JR\Tracker\DataObject\Config\AuthCookieConfig;
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

  public static function fromAuthCookieConfig(AuthCookieConfig $config): self
  {
    return new self(
      secure: $config->secure,
      httpOnly: $config->httpOnly,
      sameSite: $config->sameSite,
      expires: $config->expires,
      path: $config->path,
    );
  }
}
