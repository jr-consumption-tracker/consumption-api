<?php

declare(strict_types=1);

namespace JR\Tracker\Repository\Implementation;

use JR\Tracker\Entity\User\Implementation\UserVerifyEmail;
use JR\Tracker\Entity\User\Contract\UserVerifyEmailInterface;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;
use JR\Tracker\Repository\Contract\VerifyEmailRepositoryInterface;

class VerifyEmailRepository implements VerifyEmailRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManagerService,
    ) {
    }

    public function getActiveTokenByEmail(string $email): ?UserVerifyEmailInterface
    {
        $repository = $this->entityManagerService->getRepository(UserVerifyEmail::class);

        $qb = $repository->createQueryBuilder('uve');
        $qb
            ->where('uve.email = :email')
            ->andWhere('uve.usedAt IS NULL')
            ->andWhere('uve.expiresAt > :now')
            ->setParameter('email', $email)
            ->setParameter('now', new \DateTimeImmutable());

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function updateVerifyEmail(UserVerifyEmailInterface $userVerifyEmil): void
    {
        $this->entityManagerService->sync($userVerifyEmil);
    }

    public function createVerifyEmail(UserVerifyEmailInterface $userVerifyEmil): int
    {
        return $this->entityManagerService->sync($userVerifyEmil);
    }

}