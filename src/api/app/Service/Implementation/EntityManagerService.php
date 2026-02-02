<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use Doctrine\ORM\EntityManagerInterface;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;

/**
 * @mixin EntityManagerInterface
 */
class EntityManagerService implements EntityManagerServiceInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManagerService
    ) {
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->entityManagerService, $name)) {
            return call_user_func_array([$this->entityManagerService, $name], $arguments);
        }

        throw new \BadMethodCallException('Call to undefined method "' . $name . '"');
    }

    public function sync($entity = null): int
    {
        if ($entity) {
            $this->entityManagerService->persist($entity);
        }

        $this->entityManagerService->flush();

        return (int) $this->entityManagerService->getConnection()->lastInsertId();
    }

    public function delete($entity, bool $sync = false): void
    {
        $this->entityManagerService->remove($entity);

        if ($sync) {
            $this->sync();
        }
    }

    public function clear(?string $entityName = null): void
    {
        if ($entityName === null) {
            $this->entityManagerService->clear();

            return;
        }

        $unitOfWork = $this->entityManagerService->getUnitOfWork();
        $entities = $unitOfWork->getIdentityMap()[$entityName] ?? [];

        foreach ($entities as $entity) {
            $this->entityManagerService->detach($entity);
        }
    }

    public function enableUserAuthFilter(int $idUser): void
    {
        $this->getFilters()->enable('user')->setParameter('idUser', $idUser);
    }
}