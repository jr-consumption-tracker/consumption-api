<?php

declare(strict_types=1);

namespace JR\Tracker\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// Appka je SPA na samostatné doméně/cestě, ne server-rendered appka s Twig
// šablonami - Guard sice na každý request sám připojí aktuální csrf_name/
// csrf_value jako atributy requestu, ale nikam v HTML je vložit nejde.
// Musí se dostat k JS klientovi prostřednictvím response hlaviček - klient
// si je uloží do paměti (ne do cookie, tu JS záměrně nesmí číst) a pošle
// zpátky na mutujících requestech.
class ExposeCsrfTokenMiddleware implements MiddlewareInterface
{
  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    $response = $handler->handle($request);

    $csrfName = $request->getAttribute('csrf_name');
    $csrfValue = $request->getAttribute('csrf_value');

    if (!is_string($csrfName) || !is_string($csrfValue)) {
      return $response;
    }

    return $response
      ->withHeader('csrf_name', $csrfName)
      ->withHeader('csrf_value', $csrfValue);
  }
}
