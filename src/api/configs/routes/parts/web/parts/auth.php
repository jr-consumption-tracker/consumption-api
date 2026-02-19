<?php

declare(strict_types=1);

use JR\Tracker\Controller\Web\AuthController;
use JR\Tracker\Middleware\RateLimitMiddleware;
use Slim\Routing\RouteCollectorProxy;

function getWebAuthRoutes(RouteCollectorProxy $api)
{
  $api->group('/auth', function (RouteCollectorProxy $auth) {
    $auth->post('/register', [AuthController::class, "register"]);
    $auth->post("/login", [AuthController::class, "login"])
      ->setName('web_login')
      ->add(RateLimitMiddleware::class);
    $auth->post('/logout', [AuthController::class, 'logout']);
    $auth->get('/refreshToken', [AuthController::class, 'refreshToken']);
  });

  return $api;
}
