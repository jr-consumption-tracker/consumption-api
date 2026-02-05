<?php

declare(strict_types=1);

namespace JR\Tracker\Middleware;

use JR\Tracker\Enum\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

// TODO: Toto si myslím nepotřebuji. Budu to kontrolovat jinak při přihlášení. A nebo asi ne :-) ještě uvidíme, co bude lepší
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

        return $this->responseFactory->createResponse(HttpStatusCode::FOUND->value)->withHeader('Location', '/verify');
    }
}
