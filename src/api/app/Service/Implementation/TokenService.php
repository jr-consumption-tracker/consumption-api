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
        private readonly TokenConfig $config // TODO: remove
    ) {
    }


    public function createAccessToken(UserInterface $user, array $roles, ?TokenConfig $config = null): string
    {
        $config = $config ?? $this->config;

        $payload = [
            'userInfo' => [
                'uuid' => $user->getUuid(),
                'email' => $user->getEmail(),
                'roles' => $roles
            ],
            'exp' => $config->expAccess
        ];

        return JWT::encode(
            $payload,
            $config->keyAccess,
            $config->algorithm
        );
    }

    public function createRefreshToken(UserInterface $user, ?TokenConfig $config = null): string
    {
        $config = $config ?? $this->config;

        $payload = [
            'uuid' => $user->getUuid(),
            'email' => $user->getEmail(),
            'exp' => $config->expRefresh,
        ];

        return JWT::encode(
            $payload,
            $config->keyRefresh,
            $config->algorithm
        );
    }

    public function verifyJWT(ServerRequestInterface $request, RequestHandlerInterface $handler, ?TokenConfig $config = null): ResponseInterface
    {
        $config = $config ?? $this->config;
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
            $key = new Key($config->keyAccess, $config->algorithm);
            $decoded = JWT::decode($token, $key);

            $request = $request->withAttribute('uuid', $decoded->userInfo->uuid);
            $request = $request->withAttribute('roles', $decoded->userInfo->roles);

            return $handler->handle($request);
        } catch (Exception) {
            return $handler->handle($request)->withStatus(HttpStatusCode::FORBIDDEN->value);
        }
    }

    public function decodeToken(string $token, string $tokenKey, ?string $algorithm = null): object|null
    {
        try {
            $key = new Key($tokenKey, $algorithm ?? $this->config->algorithm);

            return JWT::decode($token, $key);
        } catch (Exception) {
            return null;
        }
    }
}