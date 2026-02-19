<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

use JR\Tracker\DataObject\Data\PasswordResetData;

interface PasswordResetServiceInterface
{
  public function attemptRequest(string $email): void;

  public function attemptReset(PasswordResetData $data): void;
}
