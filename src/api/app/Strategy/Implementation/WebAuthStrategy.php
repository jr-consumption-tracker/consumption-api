<?php

declare(strict_types=1);

namespace JR\Tracker\Strategy\Implementation;

use JR\Tracker\DataObject\Config\TokenConfig;
use JR\Tracker\DataObject\Config\AuthCookieConfig;
use JR\Tracker\Strategy\Contract\AuthStrategyInterface;

class WebAuthStrategy implements AuthStrategyInterface
{
    public function __construct(
        private readonly TokenConfig $tokenConfig,
        private readonly AuthCookieConfig $authCookieConfig
    ) {
    }

    public function getTokenConfig(): TokenConfig
    {
        return $this->tokenConfig;
    }

    public function getCookieConfig(?bool $persistLogin = false): AuthCookieConfig
    {
        return new AuthCookieConfig(
            $this->authCookieConfig->name,
            $this->authCookieConfig->secure,
            $this->authCookieConfig->httpOnly,
            $this->authCookieConfig->sameSite,
            $persistLogin ? $this->authCookieConfig->expires : 0, // 0 = session
            $this->authCookieConfig->path
        );
    }
}
