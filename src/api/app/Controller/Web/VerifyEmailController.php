<?php

declare(strict_types=1);

namespace JR\Tracker\Controller\Web;

use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Service\Contract\VerifyEmailServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VerifyEmailController
{
    public function __construct(
        private readonly VerifyEmailServiceInterface $verifyEmailService
    ) {
    }

    public function verify(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $token = $queryParams["token"] ?? "";

        $this->verifyEmailService->attemptVerifyEmail($token);

        return $response->withStatus(HttpStatusCode::OK->value);
    }
}