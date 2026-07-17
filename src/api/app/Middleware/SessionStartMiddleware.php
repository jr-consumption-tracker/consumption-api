<?php

declare(strict_types=1);

namespace JR\Tracker\Middleware;

use JR\Tracker\Service\Contract\SessionServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionStartMiddleware implements MiddlewareInterface
{
  public function __construct(
    private readonly SessionServiceInterface $sessionService
  ) {
  }

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    if (!$this->sessionService->isActive()) {
      $this->sessionService->start();
    }

    return $handler->handle($request);
  }
}
