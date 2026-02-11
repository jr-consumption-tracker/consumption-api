<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use JR\Tracker\Controller\Web\VerifyEmailController;
use JR\Tracker\Middleware\ValidateSignatureMiddleware;

function getWebVerifyEmailRoutes(RouteCollectorProxy $api)
{
    $api->group('/verifyEmail', function (RouteCollectorProxy $verifyEmail) {
        $verifyEmail->post('/verify/{uuid}/{hash}', [VerifyEmailController::class, "verify"])
            ->setName('verify')
            ->add(ValidateSignatureMiddleware::class);
    });

    return $api;
}