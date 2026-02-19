<?php

declare(strict_types=1);

namespace JR\Tracker\Enum;

enum LogoutAttemptStatusEnum
{
  case NO_COOKIE;
  case NO_USER;
  case LOGOUT_SUCCESS;
}
