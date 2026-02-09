<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use JR\Tracker\Controller\Web\AuthController;
use JR\Tracker\Middleware\RateLimitMiddleware;

function getAuthRoutes(RouteCollectorProxy $api)
{
    $api->group('/auth', function (RouteCollectorProxy $auth) {
        $auth->post('/register', [AuthController::class, "register"]);
        $auth->post("/login", [AuthController::class, "login"])
            ->setName('login')
            ->add(RateLimitMiddleware::class);
    });

    return $api;
}