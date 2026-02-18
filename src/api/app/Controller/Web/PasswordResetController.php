<?php

declare(strict_types=1);

namespace JR\Tracker\Controller\Web;

use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\RequestValidator\PasswordReset\PasswordResetRequestValidator;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorFactoryInterface;
use JR\Tracker\RequestValidator\VerifyEmail\ResendVerificationRequestValidator;
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

    public function requestReset(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(PasswordResetRequestValidator::class)->validate(
            $request->getParsedBody() ?? []
        );

        $this->passwordResetService->attemptResetPassword($data["email"]);

        return $response->withStatus(HttpStatusCode::OK->value);
    }

    // public function verify(Request $request, Response $response): Response
    // {
    //     $queryParams = $request->getQueryParams();
    //     $token = $queryParams["token"] ?? "";

    //     $this->verifyEmailService->attemptVerify($token);

    //     return $response->withStatus(HttpStatusCode::OK->value);
    // }

    // public function resend(Request $request, Response $response): Response
    // {
    //     $data = $this->requestValidatorFactory->make(ResendVerificationRequestValidator::class)->validate(
    //         $request->getParsedBody() ?? []
    //     );

    //     $this->verifyEmailService->attemptResend($data["email"]);

    //     return $response->withStatus(HttpStatusCode::OK->value);
    // }
}
