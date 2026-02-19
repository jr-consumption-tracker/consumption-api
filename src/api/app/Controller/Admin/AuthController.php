<?php

declare(strict_types=1);

namespace JR\Tracker\Controller\Admin;

use JR\Tracker\DataObject\Data\LoginUserData;
use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\RequestValidator\Auth\UserLoginRequestValidator;
use JR\Tracker\RequestValidator\Request\Contract\RequestValidatorFactoryInterface;
use JR\Tracker\Service\Contract\AuthServiceInterface;
use JR\Tracker\Shared\Helper\BooleanHelper;
use JR\Tracker\Shared\ResponseFormatter\ResponseFormatter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
  public function __construct(
    private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
    private readonly AuthServiceInterface $authService,
    private readonly ResponseFormatter $responseFormatter
  ) {
  }

  /**
   * Handles user login request.
   *
   * Validates input credentials (login and password), delegates authentication to
   * the `AuthService`, and returns the resulting status or tokens as JSON.
   *
   * @param Request $request HTTP request containing login credentials
   * @param Response $response HTTP response
   * @return Response JSON response with login result
   * @throws \JR\Tracker\Exception\ValidationException When input data is invalid
   * @author Jan Ribka
   */
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

  /**
   * Handles user logout request.
   *
   * Terminates the user session for the web domain context.
   *
   * @param Request $request HTTP request
   * @param Response $response HTTP response
   * @return Response Response with 204 No Content status
   * @author Jan Ribka
   */
  public function logout(Request $request, Response $response): Response
  {
    $this->authService->attemptLogout(DomainContextEnum::ADMIN);

    return $response->withStatus(HttpStatusCode::NO_CONTENT->value);
  }

  /**
   * Handles token refresh request.
   *
   * Refreshes the authentication token for the current user session based on provided
   * query parameters, specifically the persistLogin flag to maintain session persistence.
   *
   * @param Request $request HTTP request containing query parameters (persistLogin)
   * @param Response $response HTTP response
   * @return Response JSON response with token refresh result
   * @author Jan Ribka
   */
  public function refreshToken(Request $request, Response $response): Response
  {
    $parseBoolean = BooleanHelper::parse();

    $queryParams = $request->getQueryParams();
    $persistLogin = $parseBoolean($queryParams['persistLogin'] ?? false);
    $credentials = ['persistLogin' => $persistLogin];

    $result = $this->authService->attemptRefreshToken($credentials, DomainContextEnum::ADMIN);

    return $this->responseFormatter->asJson($response, $result);
  }
}
