<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\DataObject\Config\SessionConfig;
use JR\Tracker\Exception\SessionException;
use JR\Tracker\Service\Contract\SessionServiceInterface;

class SessionService implements SessionServiceInterface
{
  public function __construct(
    private readonly SessionConfig $config
  ) {
  }

  public function start(): void
  {
    if ($this->isActive()) {
      throw new SessionException('Session has already been started');
    }

    if (headers_sent($fileName, $line)) {
      throw new SessionException('Headers have already sent by ' . $fileName . ':' . $line);
    }

    session_set_cookie_params(
      [
        'secure' => $this->config->secure,
        'httponly' => $this->config->httpOnly,
        'samesite' => $this->config->sameSite->value,
      ]
    );

    if (!empty($this->config->name)) {
      session_name($this->config->name);
    }

    if (!session_start()) {
      throw new SessionException('Unable to start the session');
    }
  }

  public function save(): void
  {
    session_write_close();
  }

  public function isActive(): bool
  {
    return session_status() === PHP_SESSION_ACTIVE;
  }

  public function get(string $key, mixed $default = null): mixed
  {
    return $this->has($key) ? $_SESSION[$key] : $default;
  }

  public function has(string $key): bool
  {
    return array_key_exists($key, $_SESSION);
  }

  public function regenerate(): bool
  {
    return session_regenerate_id();
  }

  public function put(string $key, mixed $value): void
  {
    $_SESSION[$key] = $value;
  }

  public function forget(string $key): void
  {
    unset($_SESSION[$key]);
  }

  public function flash(string $key, array $messages): void
  {
    $_SESSION[$this->config->flashName][$key] = $messages;
  }

  public function getFlash(string $key): array
  {
    $messages = $_SESSION[$this->config->flashName][$key] ?? [];

    unset($_SESSION[$this->config->flashName][$key]);

    return $messages;
  }

  public function destroy(): void
  {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();

      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }

    session_destroy();
  }
}
