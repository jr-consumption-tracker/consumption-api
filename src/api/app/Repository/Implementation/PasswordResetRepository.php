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
        private readonly EntityManagerServiceInterface $entityManager,
    ) {
    }

    public function create(UserPasswordResetInterface $passwordReset): void
    {
        $this->entityManager->sync($passwordReset);
    }

    public function update(UserPasswordResetInterface $passwordReset): void
    {
        $this->entityManager->sync($passwordReset);
    }

    public function getActiveToken(string $email): ?UserPasswordResetInterface
    {
        return $this->entityManager->getRepository(UserPasswordReset::class)
            ->findOneBy(
                [
                    'email' => $email,
                ]
            );
    }
}