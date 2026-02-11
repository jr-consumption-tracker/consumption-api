<?php

declare(strict_types=1);

namespace JR\Tracker\Strategy\Implementation;

use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\Strategy\Contract\AuthStrategyInterface;
use JR\Tracker\Strategy\Contract\AuthStrategyFactoryInterface;

class AuthStrategyFactory implements AuthStrategyFactoryInterface
{
    public function __construct(
        private readonly WebAuthStrategy $webAuthStrategy,
        private readonly AdminAuthStrategy $adminAuthStrategy
    ) {
    }

    public function create(DomainContextEnum $domain): AuthStrategyInterface
    {
        return match ($domain) {
            DomainContextEnum::WEB => $this->webAuthStrategy,
            DomainContextEnum::ADMIN => $this->adminAuthStrategy,
        };
    }
}
