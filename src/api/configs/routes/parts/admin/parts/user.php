<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;

function getAdminUserRoutes(RouteCollectorProxy $api)
{
    $api->group('/user', function (RouteCollectorProxy $user) {
        // $user->get('/profile', \App\Api\Handlers\User\GetProfileHandler::class);

    });

    return $api;
}