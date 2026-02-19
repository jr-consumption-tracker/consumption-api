<?php

declare(strict_types=1);

namespace JR\Tracker\Repository\Implementation;

use JR\Tracker\Entity\User\Contract\UserPasswordResetInterface;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;
use JR\Tracker\Entity\User\Implementation\UserPasswordReset;
use JR\Tracker\Repository\Contract\PasswordResetRepositoryInterface;

class PasswordResetRepository implements PasswordResetRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManagerService,
    ) {
    }

    public function create(UserPasswordResetInterface $passwordReset): void
    {
        $this->entityManagerService->sync($passwordReset);
    }

    public function update(UserPasswordResetInterface $passwordReset): void
    {
        $this->entityManagerService->sync($passwordReset);
    }

    public function getByEmail(string $email): ?UserPasswordResetInterface
    {
        return $this->entityManagerService->getRepository(UserPasswordReset::class)
            ->findOneBy(
                [
                    'email' => $email,
                ]
            );
    }

    public function getByToken(string $token): ?UserPasswordResetInterface
    {
        return $this->entityManagerService->getRepository(UserPasswordReset::class)
            ->findOneBy(['token' => $token]);
    }

    public function delete(string $token): void
    {
        $tokenEntity = $this->getByToken($token);

        if (isset($tokenEntity)) {
            $this->entityManagerService->remove($tokenEntity);
            $this->entityManagerService->flush();
        }
    }
}