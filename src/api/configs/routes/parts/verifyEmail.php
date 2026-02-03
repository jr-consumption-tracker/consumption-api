<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use JR\Tracker\Controller\VerifyEmailController;
use JR\Tracker\Middleware\ValidateSignatureMiddleware;

function getVerifyEmailRoutes(RouteCollectorProxy $api)
{
    $api->group('/verifyEmil', function (RouteCollectorProxy $auth) {
        $auth->post('/verify/{uuid}/{hash}', [VerifyEmailController::class, "verify"])
            ->setName('verify')
            ->add(ValidateSignatureMiddleware::class);
    });

    return $api;
}