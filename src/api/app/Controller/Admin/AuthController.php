<?php

declare(strict_types=1);

namespace JR\Tracker\Controller\Admin;

use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\Shared\Helper\BooleanHelper;
use JR\Tracker\DataObject\Data\LoginUserData;
use Psr\Http\Message\ResponseInterface as Response;
use JR\Tracker\Service\Contract\AuthServiceInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use JR\Tracker\Shared\ResponseFormatter\ResponseFormatter;
use JR\Tracker\RequestValidator\Auth\UserLoginRequestValidator;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorFactoryInterface;


class AuthController
{
    public function __construct(
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly AuthServiceInterface $authService,
        private readonly ResponseFormatter $responseFormatter
    ) {
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(UserLoginRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $parseBoolean = BooleanHelper::parse();

        $loginResult = $this->authService->attemptLogin(
            new LoginUserData(
                $data['email'],
                $data['password'],
                $parseBoolean(($data['persistLogin'] ?? false))
            ),
            DomainContextEnum::ADMIN
        );

        return $this->responseFormatter->asJson($response, $loginResult);
    }

    public function logout(Request $request, Response $response): Response
    {
        $this->authService->attemptLogout(DomainContextEnum::ADMIN);

        return $response->withStatus(HttpStatusCode::NO_CONTENT->value);
    }
}