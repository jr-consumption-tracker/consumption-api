<?php

declare(strict_types=1);

use JR\Tracker\Controller\Web\VerifyEmailController;
use JR\Tracker\Middleware\RateLimitMiddleware;
use Slim\Routing\RouteCollectorProxy;

function getWebVerifyEmailRoutes(RouteCollectorProxy $api): RouteCollectorProxy
{
  $api->group('/verifyEmail', function (RouteCollectorProxy $verifyEmail) {
    $verifyEmail->post('/verify', [VerifyEmailController::class, "verify"])
      ->setName('web_verifyEmail')
      ->add(RateLimitMiddleware::class);
    $verifyEmail->post("/resend", [VerifyEmailController::class, 'resend'])
      ->setName('web_resend')
      ->add(RateLimitMiddleware::class);
  });

  return $api;
}
