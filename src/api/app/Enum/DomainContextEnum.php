<?php

declare(strict_types=1);

namespace JR\Tracker\Enum;

enum DomainContextEnum: string
{
    case WEB = 'web';
    case ADMIN = 'admin';
}