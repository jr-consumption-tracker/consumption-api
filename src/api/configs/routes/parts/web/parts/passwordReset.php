<?php

declare(strict_types=1);

use JR\Tracker\Controller\Web\PasswordResetController;
use Slim\Routing\RouteCollectorProxy;
use JR\Tracker\Middleware\RateLimitMiddleware;

function getWebPasswordResetRoutes(RouteCollectorProxy $api): RouteCollectorProxy
{
    $api->group('/passwordReset', function (RouteCollectorProxy $passwordReset) {
        $passwordReset->post('/request', [PasswordResetController::class, "request"])
            ->setName('web_requestPasswordReset')
            ->add(RateLimitMiddleware::class);
        $passwordReset->post('/reset', [PasswordResetController::class, "reset"])
            ->setName('web_resetPassword')
            ->add(RateLimitMiddleware::class);
    });

    return $api;
}