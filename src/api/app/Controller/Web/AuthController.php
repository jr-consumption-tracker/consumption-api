<?php

declare(strict_types=1);

namespace JR\Tracker\Controller\Web;

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
// Pro oddělen é příhlašování použít strategy pattern
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