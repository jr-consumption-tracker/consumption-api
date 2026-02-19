<?php

declare(strict_types=1);

use JR\Tracker\Enum\AppEnvironmentEnum;

$appEnv = $_ENV['APP_ENV'] ?? AppEnvironmentEnum::Production->value;
$isDevelopment = AppEnvironmentEnum::isDevelopment($appEnv);

if ($isDevelopment) {
  header("Access-Control-Allow-Origin: " . $_ENV["ALLOWED_ORIGINS"]);
  header("Access-Control-Allow-Methods: *");
  header("Access-Control-Allow-Headers: *");
}
