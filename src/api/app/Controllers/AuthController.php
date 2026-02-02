<?php

declare(strict_types=1);

namespace JR\Tracker\Controllers;

use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\DataObject\Data\RegisterUserData;
use Psr\Http\Message\ResponseInterface as Response;
use JR\Tracker\Service\Contract\AuthServiceInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use JR\Tracker\RequestValidator\Auth\RegisterUserRequestValidator;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorFactoryInterface;

class AuthController
{
    public function __construct(
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly AuthServiceInterface $authService,
    ) {
    }

    public function registerUser(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(RegisterUserRequestValidator::class)
            ->validate(
                $request->getParsedBody() ?? []
            );

        $this->authService->registerUser(
            new RegisterUserData(
                $data['email'],
                $data['password']
            )
        );

        return $response->withStatus(HttpStatusCode::CREATED->value);
    }
}