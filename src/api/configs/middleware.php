<?php

declare(strict_types=1);

use Clockwork\Clockwork;
use Clockwork\Support\Slim\ClockworkMiddleware;
use JR\Tracker\Config;
use JR\Tracker\Enum\AppEnvironmentEnum;
use JR\Tracker\Middleware\StartSessionMiddleware;
use JR\Tracker\Middleware\ValidationExceptionMiddleware;
use JR\Tracker\Middleware\VerificationExceptionMiddleware;
use Slim\App;

return function (App $app) {
  $container = $app->getContainer();
  $config = $container->get(Config::class);

  if ($config->get('csrf_enabled')) {
    $app->add('csrf');
  }

  $app->add(VerificationExceptionMiddleware::class);
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
