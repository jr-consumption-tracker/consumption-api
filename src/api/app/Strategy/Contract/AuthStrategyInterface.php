<?php

declare(strict_types=1);

namespace JR\Tracker\Strategy\Contract;

use JR\Tracker\DataObject\Config\TokenConfig;
use JR\Tracker\DataObject\Config\AuthCookieConfig;

interface AuthStrategyInterface
{
    public function getTokenConfig(): TokenConfig;
    public function getCookieConfig(?bool $persistLogin = false): AuthCookieConfig;
}
