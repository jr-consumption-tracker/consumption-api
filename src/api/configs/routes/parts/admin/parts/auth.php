<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use JR\Tracker\Middleware\RateLimitMiddleware;
use JR\Tracker\Controller\Admin\AuthController;

function getAdminAuthRoutes(RouteCollectorProxy $api)
{
    $api->group('/auth', function (RouteCollectorProxy $auth) {
        $auth->post("/login", [AuthController::class, "login"])
            ->setName('admin_login')
            ->add(RateLimitMiddleware::class);
        $auth->post('/logout', [AuthController::class, 'logout']);
        $auth->get('/refreshToken', [AuthController::class, 'refreshToken']);
    });

    return $api;
}