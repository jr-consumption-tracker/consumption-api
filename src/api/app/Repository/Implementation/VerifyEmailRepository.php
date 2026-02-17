<?php

declare(strict_types=1);

namespace JR\Tracker\Repository\Implementation;

use JR\Tracker\Repository\Contract\VerifyEmailRepositoryInterface;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;
use JR\Tracker\Entity\User\Contract\UserVerifyEmailInterface;
use JR\Tracker\Entity\User\Implementation\UserVerifyEmail;

class VerifyEmailRepository implements VerifyEmailRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManager,
    ) {
    }

    public function createVerifyEmail(UserVerifyEmailInterface $verifyEmail): void
    {
        $this->entityManager->sync($verifyEmail);
    }

    public function updateVerifyEmail(UserVerifyEmailInterface $verifyEmail): void
    {
        $this->entityManager->sync($verifyEmail);
    }

    public function getActiveTokenByEmail(string $email): ?UserVerifyEmailInterface
    {
        return $this->entityManager->getRepository(UserVerifyEmail::class)
            ->findOneBy(
                [
                    'email' => $email,
                ]
            );
    }
}