<?php

declare(strict_types=1);

namespace JR\Tracker\Shared\Interface;

use JR\Tracker\Entity\User\Contract\UserInterface;

interface OwnableInterface
{
  public function getUser(): UserInterface;
}
