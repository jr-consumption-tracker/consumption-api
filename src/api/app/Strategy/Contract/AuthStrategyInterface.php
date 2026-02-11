<?php

declare(strict_types=1);

namespace JR\Tracker\Strategy\Contract;

use JR\Tracker\DataObject\Config\TokenConfig;
use JR\Tracker\DataObject\Config\AuthCookieConfig;
use JR\Tracker\Entity\User\Contract\UserInterface;

interface AuthStrategyInterface
{
    public function getTokenConfig(): TokenConfig;
    public function getCookieConfig(?bool $persistLogin = false): AuthCookieConfig;
    public function verifyUser(?UserInterface $user, string $password): void;
}
