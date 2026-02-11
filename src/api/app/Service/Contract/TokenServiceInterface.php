<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\DataObject\Config\TokenConfig;

interface TokenServiceInterface
{
    /**
     * Create access token
     * @param \JR\Tracker\Entity\User\Contract\UserInterface $user
     * @param int[] $roles
     * @return string
     * @author Jan Ribka
     */
    public function createAccessToken(UserInterface $user, array $roles, TokenConfig $config): string;

    public function createRefreshToken(UserInterface $user, TokenConfig $config): string;

    public function verifyJWT(ServerRequestInterface $request, RequestHandlerInterface $handler, TokenConfig $config): ResponseInterface;

    public function decodeToken(string $token, string $tokenKey, string $algorithm): object|null;
}

