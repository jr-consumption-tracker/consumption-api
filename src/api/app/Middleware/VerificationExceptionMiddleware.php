<?php

declare(strict_types=1);

namespace JR\Tracker\Middleware;

use JR\Tracker\Exception\VerificationException;
use JR\Tracker\Service\Contract\RequestServiceInterface;
use JR\Tracker\Shared\ResponseFormatter\ResponseFormatter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VerificationExceptionMiddleware implements MiddlewareInterface
{
  public function __construct(
    private readonly ResponseFactoryInterface $responseFactory,
    private readonly RequestServiceInterface $requestService,
    private readonly ResponseFormatter $responseFormatter
  ) {
  }

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    try {
      return $handler->handle($request);
    } catch (VerificationException $ex) {
      $response = $this->responseFactory->createResponse();

      if ($this->requestService->isXhr($request)) {
        return $this->responseFormatter->asJson($response->withStatus($ex->getCode()), $ex->errors);
      }

      return $this->responseFormatter->asJson($response->withStatus($ex->getCode()), $ex->errors);
    }
  }
}
