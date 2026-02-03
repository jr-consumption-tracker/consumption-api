<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use JR\Tracker\Controller\AuthController;
use JR\Tracker\Middleware\RateLimitMiddleware;

function getAuthRoutes(RouteCollectorProxy $api)
{
    $api->group('/auth', function (RouteCollectorProxy $auth) {
        $auth->post('/registerUser', [AuthController::class, "registerUser"])
            ->setName('registerUser')
            ->add(RateLimitMiddleware::class);
    });

    return $api;
}