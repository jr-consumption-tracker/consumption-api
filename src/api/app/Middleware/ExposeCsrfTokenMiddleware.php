<?php

declare(strict_types=1);

namespace JR\Tracker\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Csrf\Guard;

// Appka je SPA na samostatné doméně/cestě, ne server-rendered appka s Twig
// šablonami - Guard sice na každý request sám připojí aktuální csrf_name/
// csrf_value jako atributy requestu, ale nikam v HTML je vložit nejde.
// Musí se dostat k JS klientovi prostřednictvím response hlaviček.
//
// Záměrně nečte $request->getAttribute() - tyhle atributy nastavuje Guard
// jen na $request, který sám předá dál přes $handler->handle(). Při
// neúspěšné CSRF validaci Guard $handler->handle() vůbec nezavolá
// (handleFailure() vrací rovnou svou vlastní odpověď) a při výjimce
// hlouběji v aplikaci (např. neplatná session) atributy nepřežijí -
// výjimka postup přes middleware "přeskočí". Guard si ale svůj aktuální
// token drží i jako vlastní vnitřní stav (nastavuje se jako vedlejší
// efekt při generateToken()/loadLastKeyPair(), v obou případech), takže
// se čte přímo z instance Guardu, ne z requestu - a middleware musí být
// nejvíc vnější vrstva (obaluje i middlewary, co převádí výjimky na
// odpovědi), aby zachytil úplně každou odpověď bez ohledu na to, jak
// vznikla.
class ExposeCsrfTokenMiddleware implements MiddlewareInterface
{
  public function __construct(
    private readonly ContainerInterface $container
  ) {
  }

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    $response = $handler->handle($request);

    /** @var Guard $guard */
    $guard = $this->container->get('csrf');
    $csrfName = $guard->getTokenName();
    $csrfValue = $guard->getTokenValue();

    if ($csrfName === null || $csrfValue === null) {
      return $response;
    }

    return $response
      ->withHeader('csrf_name', $csrfName)
      ->withHeader('csrf_value', $csrfValue);
  }
}
