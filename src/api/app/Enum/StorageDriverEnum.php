<?php

declare(strict_types=1);

namespace JR\Tracker\Enum;

enum StorageDriverEnum
{
    case Local;
    case Remote_DO;
}