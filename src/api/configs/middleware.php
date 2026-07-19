<?php

declare(strict_types=1);

use Clockwork\Clockwork;
use Clockwork\Support\Slim\ClockworkMiddleware;
use JR\Tracker\Config;
use JR\Tracker\Enum\AppEnvironmentEnum;
use JR\Tracker\Middleware\ExposeCsrfTokenMiddleware;
use JR\Tracker\Middleware\SessionStartMiddleware;
use JR\Tracker\Middleware\ValidationExceptionMiddleware;
use JR\Tracker\Middleware\VerificationExceptionMiddleware;
use Slim\App;

return function (App $app) {
  $container = $app->getContainer();
  $config = $container->get(Config::class);

  if ($config->get('csrf_enabled')) {
    // Slim spousti naposledy pridany middleware jako prvni. Poradi provedeni
    // (od outer po inner): SessionStart -> csrf Guard -> ExposeCsrfToken ->
    // routa. Guard musi bezet pred ExposeCsrfTokenMiddleware (potrebuje jeho
    // atributy csrf_name/csrf_value), a az po SessionStartMiddleware (ten mu
    // dodava session jako storage).
    $app->add(ExposeCsrfTokenMiddleware::class);
    $app->add('csrf');
    $app->add(SessionStartMiddleware::class);
  }

  $app->add(VerificationExceptionMiddleware::class);
  $app->add(ValidationExceptionMiddleware::class);

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
