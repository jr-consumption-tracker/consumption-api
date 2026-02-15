<?php

declare(strict_types=1);

use Slim\App;
use JR\Tracker\Config;
use Clockwork\Clockwork;
use JR\Tracker\Enum\AppEnvironmentEnum;
use Clockwork\Support\Slim\ClockworkMiddleware;
use JR\Tracker\Middleware\StartSessionMiddleware;
use JR\Tracker\Middleware\ValidationExceptionMiddleware;

return function (App $app) {
    $container = $app->getContainer();
    $config = $container->get(Config::class);

    $app->add('csrf');
    $app->add(ValidationExceptionMiddleware::class);
    $app->add(StartSessionMiddleware::class);

    if (AppEnvironmentEnum::isDevelopment($config->get('app_environment'))) {
        $app->add(new ClockworkMiddleware($app, $container->get(Clockwork::class)));
    }

    $app->addBodyParsingMiddleware();
    $app->addErrorMiddleware(
        (bool) $config->get('display_error_details'),
        (bool) $config->get('log_errors'),
        (bool) $config->get('log_error_details')
    );
};