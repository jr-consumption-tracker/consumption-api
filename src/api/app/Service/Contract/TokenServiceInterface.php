<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use JR\tracker\Entity\User\Contract\UserInterface;

interface TokenServiceInterface
{
    /**
     * Create access token
     * @param \JR\Tracker\Entity\User\Contract\UserInterface $user
     * @param int[] $role
     * @return string
     * @author Jan Ribka
     */
    public function createAccessToken(UserInterface $user, array $roles): string;

    public function createRefreshToken(UserInterface $user): string;

    public function verifyJWT(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;

    public function decodeToken(string $token, string $tokenKey): object|null;
}

