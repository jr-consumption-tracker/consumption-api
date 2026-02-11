<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('/api', function (RouteCollectorProxy $api) {
        getWebRoutes($api);
        getAdminRoutes($api);

    });

    // TODO: Na endpointy, ktere se volaji jenom kdy6 jsem prihlaseny pridat VerifyEmailMidlleware a upravit authMiddleware a napojit asi na stejne endpointy
    // TODO: Pridat ValidationSignatureMiddlewrae podle expennies
};