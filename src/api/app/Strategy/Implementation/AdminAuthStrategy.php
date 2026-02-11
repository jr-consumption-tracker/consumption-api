<?php

declare(strict_types=1);

namespace JR\Tracker\Strategy\Implementation;

use JR\Tracker\DataObject\Config\TokenConfig;
use JR\Tracker\DataObject\Config\AdminTokenConfig;
use JR\Tracker\DataObject\Config\AuthCookieConfig;
use JR\Tracker\DataObject\Config\AdminAuthCookieConfig;
use JR\Tracker\Strategy\Contract\AuthStrategyInterface;

class AdminAuthStrategy implements AuthStrategyInterface
{
    public function __construct(
        private readonly AdminTokenConfig $tokenConfig,
        private readonly AdminAuthCookieConfig $authCookieConfig
    ) {
    }

    public function getTokenConfig(): TokenConfig
    {
        return $this->tokenConfig;
    }

    public function getCookieConfig(?bool $persistLogin = false): AuthCookieConfig
    {
        // For admin, we might always want session cookie, or respect persistLogin
        return new AuthCookieConfig(
            $this->authCookieConfig->name,
            $this->authCookieConfig->secure,
            $this->authCookieConfig->httpOnly,
            $this->authCookieConfig->sameSite,
            $persistLogin ? $this->authCookieConfig->expires : 0,
            $this->authCookieConfig->path
        );
    }
}
