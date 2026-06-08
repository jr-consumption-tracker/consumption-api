<?php

declare(strict_types=1);

namespace JR\Tracker\Middleware;

use JR\Tracker\Config;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Service\Contract\RequestServiceInterface;
use JR\Tracker\Shared\ResponseFormatter\ResponseFormatter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitMiddleware implements MiddlewareInterface
{
  public function __construct(
    private readonly ResponseFactoryInterface $responseFactory,
    private readonly RequestServiceInterface $requestService,
    private readonly Config $config,
    private readonly RateLimiterFactory $rateLimiterFactory,
    private readonly ResponseFormatter $responseFormatter
  ) {
  }

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    $clientIp = $this->requestService->getClientIp($request, $this->config->get('trusted_proxies'));
    $routeContext = RouteContext::fromRequest($request);
    $route = $routeContext->getRoute();
    $limiter = $this->rateLimiterFactory->create($route->getName() . '_' . $clientIp);

    if ($limiter->consume()->isAccepted() === false) {
      $response = $this->responseFactory->createResponse();

      return $this->responseFormatter->asJson(
        $response->withStatus(HttpStatusCode::TOO_MANY_REQUESTS->value),
        ['general' => ['tooManyRequests']]
      );
    }

    return $handler->handle($request);
  }
}
