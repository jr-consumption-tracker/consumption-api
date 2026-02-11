<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;

function getAdminRoutes(RouteCollectorProxy $api)
{
    $api->group('/admin', function (RouteCollectorProxy $web) {
        getAdminAuthRoutes($web);
        getAdminUserRoutes($web);
    });

    return $api;
}