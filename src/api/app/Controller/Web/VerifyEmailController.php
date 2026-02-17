<?php

declare(strict_types=1);

namespace JR\Tracker\Controller\Web;

use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorFactoryInterface;
use JR\Tracker\RequestValidator\VerifyEmail\ResendVerificationRequestValidator;
use JR\Tracker\Service\Contract\VerifyEmailServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VerifyEmailController
{
    public function __construct(
        private readonly VerifyEmailServiceInterface $verifyEmailService,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
    ) {
    }

    public function verify(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $token = $queryParams["token"] ?? "";

        $this->verifyEmailService->attemptVerify($token);

        return $response->withStatus(HttpStatusCode::OK->value);
    }

    public function resend(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(ResendVerificationRequestValidator::class)->validate(
            $request->getParsedBody() ?? []
        );

        $this->verifyEmailService->attemptResend($data["email"]);

        return $response->withStatus(HttpStatusCode::OK->value);
    }
}
