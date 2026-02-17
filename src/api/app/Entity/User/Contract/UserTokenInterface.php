<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Contract;

use JR\Tracker\Entity\User\Implementation\UserToken;

interface UserTokenInterface
{
    public function getUser(): UserInterface;
    public function setRefreshToken(string|null $refreshToken): UserToken;
    public function setExpiresAt(\DateTime $expiresAt): self;
    public function getExpiresAt(): \DateTime;
}