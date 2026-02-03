<?php

declare(strict_types=1);

namespace JR\Tracker\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class VerifyEmailMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if ($user?->getVerifiedAt()) {
            return $handler->handle($request);
        }

        return $this->responseFactory->createResponse(302)->withHeader('Location', '/verify');
    }
}
