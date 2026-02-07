<?php

declare(strict_types=1);

namespace JR\Tracker\Enum;

enum AuthAttemptStatusEnum
{
    case FAILED;
    case TWO_FACTOR;
    case DISABLED;
}