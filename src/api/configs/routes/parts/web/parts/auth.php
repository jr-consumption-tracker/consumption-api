<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use JR\Tracker\Controller\Web\AuthController;
use JR\Tracker\Middleware\RateLimitMiddleware;

function getWebAuthRoutes(RouteCollectorProxy $api)
{
    $api->group('/auth', function (RouteCollectorProxy $auth) {
        $auth->post('/register', [AuthController::class, "register"]);
        $auth->post("/login", [AuthController::class, "login"])
            ->setName('login')
            ->add(RateLimitMiddleware::class);
        $auth->post('/logout', [AuthController::class, 'logout']);
        $auth->get('/refreshToken', [AuthController::class, 'refreshToken']);
    });

    return $api;
}