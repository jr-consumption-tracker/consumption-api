<?php

declare(strict_types=1);

namespace JR\Tracker\Enum;

enum AppEnvironmentEnum: string
{
    case Development = "development";
    case Production = "production";

    public static function isProduction(string $appEnvironment): bool
    {
        return self::tryFrom($appEnvironment) === self::Production;
    }

    public static function isDevelopment(string $appEnvironment): bool
    {
        return self::tryFrom($appEnvironment) === self::Development;
    }
}