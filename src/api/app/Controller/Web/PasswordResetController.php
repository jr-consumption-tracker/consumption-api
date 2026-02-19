<?php

declare(strict_types=1);

namespace JR\Tracker\Controller\Web;

use JR\Tracker\DataObject\Data\PasswordResetData;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\RequestValidator\PasswordReset\RequestPasswordResetRequestValidator;
use JR\Tracker\RequestValidator\PasswordReset\ResetPasswordRequestValidator;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorFactoryInterface;
use JR\Tracker\Service\Contract\PasswordResetServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PasswordResetController
{
  public function __construct(
    private readonly PasswordResetServiceInterface $passwordResetService,
    private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
  ) {
  }

  public function request(Request $request, Response $response): Response
  {
    $data = $this->requestValidatorFactory->make(RequestPasswordResetRequestValidator::class)->validate(
      $request->getParsedBody() ?? []
    );

    $this->passwordResetService->attemptRequest($data["email"]);

    return $response->withStatus(HttpStatusCode::OK->value);
  }

  public function reset(Request $request, Response $response): Response
  {
    $data = $this->requestValidatorFactory->make(ResetPasswordRequestValidator::class)->validate(
      $request->getParsedBody() ?? []
    );

    $this->passwordResetService->attemptReset(
      new PasswordResetData(
        $data["password"],
        $data["confirmPassword"],
        $data["token"]
      )
    );

    return $response->withStatus(HttpStatusCode::OK->value);
  }
}
