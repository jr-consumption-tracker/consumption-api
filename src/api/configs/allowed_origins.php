<?php

declare(strict_types=1);

use JR\Tracker\Enum\AppEnvironmentEnum;

$appEnv = $_ENV['APP_ENV'] ?? AppEnvironmentEnum::Production->value;
$isDevelopment = AppEnvironmentEnum::isDevelopment($appEnv);

if ($isDevelopment) {
  $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
  $allowedOrigins = array_map('trim', explode(',', $_ENV['ALLOWED_ORIGINS'] ?? ''));

  if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: " . $origin);
  }

  header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
  header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, csrf_name, csrf_value");
  header("Access-Control-Expose-Headers: csrf_name, csrf_value");
  header("Access-Control-Allow-Credentials: true");

  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Max-Age: 1728000");
    header("Content-Type: text/plain charset=UTF-8");
    header("Content-Length: 0");
    http_response_code(204);
    exit();
  }
}
