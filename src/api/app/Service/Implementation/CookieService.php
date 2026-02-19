<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\DataObject\Data\CookieConfigData;
use JR\Tracker\Service\Contract\CookieServiceInterface;

class CookieService implements CookieServiceInterface
{
  public function __construct()
  {
  }

  public function start(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  public function set(string $key, string $value, CookieConfigData|null $config = null): void
  {
    setcookie($key, $value, [
      'expires' => $config?->expires,
      'path' => $config?->path,
      'httpOnly' => $config?->httpOnly,
      'secure' => $config?->secure,
      'sameSite' => $config?->sameSite->value,
    ]);
  }

  public function get(string $key): string|null
  {
    return $_COOKIE[$key] ?? null;
  }

  public function delete(string $key, CookieConfigData|null $config = null): void
  {
    setcookie($key, "", [
      'expires' => time() - 3600,
      'path' => $config?->path,
      'httpOnly' => $config?->httpOnly,
      'secure' => $config?->secure,
      'sameSite' => $config?->sameSite->value,
    ]);
  }

  public function exists(string $key): bool
  {
    return isset($_COOKIE[$key]);
  }
}
