<?php

declare(strict_types=1);

namespace JR\Tracker\Controller\Web;

use JR\Tracker\Enum\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class VerifyEmailController
{
    public function verify(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $response->withStatus(HttpStatusCode::FOUND->value);
    }
}