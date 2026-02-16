<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use JR\Tracker\Controller\Web\VerifyEmailController;
use JR\Tracker\Middleware\RateLimitMiddleware;

function getWebVerifyEmailRoutes(RouteCollectorProxy $api): RouteCollectorProxy
{
    $api->group('/verifyEmail', function (RouteCollectorProxy $verifyEmail) {
        $verifyEmail->post('/verify', [VerifyEmailController::class, "verify"])
            ->add(RateLimitMiddleware::class);
    });

    return $api;
}