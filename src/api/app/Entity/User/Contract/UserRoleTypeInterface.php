<?php

declare(strict_types=1);

namespace JR\Tracker\Entity\User\Contract;

use Doctrine\Common\Collections\Collection;

interface UserRoleTypeInterface
{
    // Getters
    public function getValue(): int;
    public function getUserRolePermission(): Collection;

    // Setters
    public function setCode(string $code): self;
    public function setValue(int $value): self;
    public function setDescription(string $description): self;
}