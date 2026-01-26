<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/configs/path_constants.php';
require __DIR__ . '/constants/regexes.php';
require __DIR__ . '/configs/routes/parts/parts.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

require CONFIG_PATH . '/allowed_origins.php';

return require CONFIG_PATH . '/container/container.php';

