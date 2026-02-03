<?php

declare(strict_types=1);

namespace JR\Tracker\Enum;

enum SameSiteEnum: string
{
    case STRICT = 'strict';
    case LAX = 'lax';
    case NONE = 'none';
}