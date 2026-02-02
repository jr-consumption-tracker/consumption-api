<?php

declare(strict_types=1);

namespace JR\Tracker\Enum;

enum UserRoleTypeEnum: int
{
    case AUDITOR = 7625;
    case SUPPORT = 4468;
    case EDITOR = 1984;
    case MODERATOR = 3128;
    case ADMIN = 5150;
    case SUPER_ADMIN = 2821;
}