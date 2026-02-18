<?php

declare(strict_types=1);

use JR\Tracker\Controller\Web\PasswordResetController;
use Slim\Routing\RouteCollectorProxy;
use JR\Tracker\Middleware\RateLimitMiddleware;

function getWebPasswordResetRoutes(RouteCollectorProxy $api): RouteCollectorProxy
{
    $api->group('/passwordReset', function (RouteCollectorProxy $passwordReset) {
        $passwordReset->post('/requestReset', [PasswordResetController::class, "requestReset"])
            ->setName('web_requestReset')
            ->add(RateLimitMiddleware::class);
    });

    return $api;
}