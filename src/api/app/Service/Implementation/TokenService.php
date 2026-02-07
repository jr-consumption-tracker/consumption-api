<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use JR\Tracker\Enum\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use JR\Tracker\DataObject\Config\TokenConfig;
use Psr\Http\Message\ResponseFactoryInterface;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Service\Contract\TokenServiceInterface;

class TokenService implements TokenServiceInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly TokenConfig $config
    ) {
    }


    public function createAccessToken(UserInterface $user, array $roles): string
    {
        $payload = [
            'userInfo' => [
                'uuid' => $user->getUuid(),
                'email' => $user->getEmail(),
                'roles' => $roles
            ],
            'exp' => $this->config->expAccess
        ];

        return JWT::encode(
            $payload,
            $this->config->keyAccess,
            $this->config->algorithm
        );
    }

    public function createRefreshToken(UserInterface $user): string
    {
        $payload = [
            'uuid' => $user->getUuid(),
            'email' => $user->getEmail(),
            'exp' => $this->config->expRefresh,
        ];

        return JWT::encode(
            $payload,
            $this->config->keyRefresh,
            $this->config->algorithm
        );
    }

    public function verifyJWT(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('HTTP_AUTHORIZATION');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->responseFactory->createResponse(HttpStatusCode::UNAUTHORIZED->value);
        }

        $tokenParts = explode(' ', $authHeader);

        if (count($tokenParts) !== 2) {
            return $handler->handle($request)->withStatus(HttpStatusCode::UNAUTHORIZED->value);
        }

        $token = explode(' ', $authHeader)[1];

        try {
            $key = new Key($this->config->keyAccess, $this->config->algorithm);
            $decoded = JWT::decode($token, $key);

            $request = $request->withAttribute('uuid', $decoded->userInfo->uuid);
            $request = $request->withAttribute('roles', $decoded->userInfo->roles);

            return $handler->handle($request);
        } catch (Exception) {
            return $handler->handle($request)->withStatus(HttpStatusCode::FORBIDDEN->value);
        }
    }

    public function decodeToken(string $token, string $tokenKey): object|null
    {
        try {
            $key = new Key($tokenKey, $this->config->algorithm);

            return JWT::decode($token, $key);
        } catch (Exception) {
            return null;
        }
    }
}