<?php

declare(strict_types=1);

use JR\Tracker\Enum\AppEnvironmentEnum;

$boolean = function (mixed $value) {
    if (in_array($value, ['true', 1, '1', true, 'yes', 'on'], true)) {
        return true;
    }

    return false;
};
$appEnv = $_ENV['APP_ENV'] ?? AppEnvironmentEnum::Production->value;
$appSnakeName = strtolower(str_replace(' ', '_', $_ENV['APP_NAME']));


return [
    'app_key' => $_ENV['APP_KEY'] ?? '',
    'app_name' => $_ENV['APP_NAME'],
    'app_version' => $_ENV['APP_VERSION'] ?? '1.0',
    'app_url' => $_ENV['APP_URL'],
    'app_environment' => $appEnv,
    'display_error_details' => $boolean($_ENV['APP_DEBUG'] ?? 0),
    'log_errors' => true,
    'log_error_details' => true,
    'doctrine' => [
        'dev_mode' => AppEnvironmentEnum::isDevelopment($appEnv),
        'cache_dir' => STORAGE_PATH . '/cache/doctrine',
        'entity_dir' => [APP_PATH . '/Entity'],
        'connection' => [
            'driver' => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
        ],
    ],
];


