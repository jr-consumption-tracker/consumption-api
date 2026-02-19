<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

interface HashServiceInterface
{
  public function hash(string $input): string;
}
