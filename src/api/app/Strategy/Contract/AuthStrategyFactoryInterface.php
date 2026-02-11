<?php

declare(strict_types=1);

namespace JR\Tracker\Strategy\Contract;

use JR\Tracker\Enum\DomainContextEnum;

interface AuthStrategyFactoryInterface
{
    public function create(DomainContextEnum $domain): AuthStrategyInterface;
}
