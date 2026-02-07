<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @mixin EntityManagerInterface
 * @author Jan Ribka
 * @copyright (c) 2024
 */
interface EntityManagerServiceInterface
{
    public function __call(string $name, array $arguments);

    public function sync($entity = null): int|string|null;

    public function delete($entity, bool $sync = false): void;

    public function clear(?string $entityName = null): void;

    public function enableUserAuthFilter(int $userId): void;
}

