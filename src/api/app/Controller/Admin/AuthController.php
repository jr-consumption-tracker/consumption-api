<?php

declare(strict_types=1);

namespace JR\Tracker\Controller\Admin;

use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Enum\DomainContextEnum;
use Psr\Http\Message\ResponseInterface as Response;
use JR\Tracker\Service\Contract\AuthServiceInterface;
use Psr\Http\Message\ServerRequestInterface as Request;


class AuthController
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {
    }

    public function logout(Request $request, Response $response): Response
    {
        $this->authService->attemptLogout(DomainContextEnum::ADMIN);

        return $response->withStatus(HttpStatusCode::NO_CONTENT->value);
    }
}