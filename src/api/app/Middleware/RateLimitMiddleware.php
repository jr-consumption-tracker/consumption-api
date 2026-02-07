<?php

declare(strict_types=1);

namespace JR\Tracker\Middleware;

use JR\Tracker\Config;
use JR\Tracker\Enum\HttpStatusCode;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use JR\Tracker\Service\Contract\RequestServiceInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly RequestServiceInterface $requestService,
        private readonly Config $config,
        private readonly RateLimiterFactory $rateLimiterFactory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $clientIp = $this->requestService->getClientIp($request, $this->config->get('trusted_proxies'));
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $limiter = $this->rateLimiterFactory->create($route->getName() . '_' . $clientIp);

        if ($limiter->consume()->isAccepted() === false) {
            return $this->responseFactory->createResponse(HttpStatusCode::TOO_MANY_REQUESTS->value, 'Too many requests');
        }

        return $handler->handle($request);
    }
}
