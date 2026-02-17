<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;

function getWebRoutes(RouteCollectorProxy $api)
{
    $api->group('/web', function (RouteCollectorProxy $web) {
        getWebAuthRoutes($web);
        getWebVerifyEmailRoutes($web);
        getWebUserRoutes($web);
    });

    return $api;
}