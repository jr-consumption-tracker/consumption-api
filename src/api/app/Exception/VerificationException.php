<?php

declare(strict_types=1);

namespace JR\Tracker\Exception;

use RuntimeException;
use Throwable;

class VerificationException extends RuntimeException
{
  public function __construct(
    public readonly array $errors,
    int $code,
    string $message = "Chyba ověření",
    ?Throwable $previous = null
  ) {
    parent::__construct($message, $code, $previous);
  }
}
