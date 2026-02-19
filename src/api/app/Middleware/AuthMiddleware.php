<?php

declare(strict_types=1);

namespace JR\Tracker\Middleware;

use JR\Tracker\DataObject\Config\AuthCookieConfig;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Service\Contract\CookieServiceInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
  public function __construct(
    private readonly CookieServiceInterface $cookieService,
    private readonly AuthCookieConfig $authCookieConfig,
    private readonly ResponseFactoryInterface $responseFactory,
  ) {
  }

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    $authCookie = $this->cookieService->get($this->authCookieConfig->name);

    if (!isset($authCookie)) {
      // TODO: Měl bych yu spíše vracet 401 Unauthorized, ale ověřit, zda se mi poak nebude obnovovat token donekonečna
      return $this->responseFactory->createResponse()->withStatus(HttpStatusCode::NO_CONTENT->value);
    }

    return $handler->handle($request);
  }
}
