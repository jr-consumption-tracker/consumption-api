<?php

declare(strict_types=1);

namespace JR\Tracker\Repository\Implementation;

use JR\Tracker\Enum\UserRoleTypeEnum;
use JR\Tracker\Enum\DomainContextEnum;
use JR\Tracker\Entity\User\Implementation\User;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Implementation\UserInfo;
use JR\Tracker\Entity\User\Implementation\UserRole;
use JR\Tracker\Entity\User\Implementation\UserToken;
use JR\Tracker\Entity\User\Contract\UserTokenInterface;
use JR\Tracker\Entity\User\Implementation\UserRoleType;
use JR\Tracker\Entity\User\Implementation\UserLoginHistory;
use JR\Tracker\Entity\User\Implementation\UserVerifyEmail;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManagerService,
    ) {

    }

    public function create(RegisterUserData $data): UserInterface
    {
        $user = new User();

        $this->entityManagerService->wrapInTransaction(function () use ($user, $data) {
            // Insert user
            $user
                ->setUuid()
                ->setEmail($data->email)
                ->setPassword($data->password)
                ->setCreatedAt();

            $this->entityManagerService->sync($user);

            // Insert user info
            $userInfo = new UserInfo()
                ->setUser($user);

            $this->entityManagerService->sync($userInfo);

            // Insert user role types
            $userRoleTypes = $this->entityManagerService->getRepository(UserRoleType::class)
                ->findBy(
                    [
                        'value' =>
                            [
                                UserRoleTypeEnum::EDITOR->value
                            ]
                    ]
                );

            foreach ($userRoleTypes as $item) {
                $userRole = new UserRole();
                $userRole
                    ->setUser($user)
                    ->setUserRoleType($item);

                $this->entityManagerService->persist($userRole);
            }

            $this->entityManagerService->flush();
            $this->entityManagerService->clear();
        });

        return $user;
    }

    public function update(UserInterface $user): void
    {
        $this->entityManagerService->sync($user);
    }

    public function getByEmail(string $email): ?UserInterface
    {
        return $this->entityManagerService->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    public function logLoginAttempt(DomainContextEnum $domain, UserInterface $user, bool $successful): void
    {
        $userLogHistory = new UserLoginHistory();

        $userLogHistory
            ->setContext($domain)
            ->setLoginAttemptAt(new \DateTimeImmutable())
            ->setIsSuccessful($successful)
            ->setUser($user);

        if (isset($user)) {
            $this->entityManagerService->sync($userLogHistory);
        }
    }

    public function getRoleByIdUser(string $idUser): array
    {
        return $this->entityManagerService->getRepository(UserRole::class)
            ->findBy(['user' => $idUser]);
    }

    public function refreshTokenExists(string $refreshToken): bool
    {
        return !!$this->entityManagerService->getRepository(UserToken::class)
            ->findOneBy(['refreshToken' => $refreshToken]);
    }

    public function deleteRefreshTokes(string $idUser, DomainContextEnum $domain): void
    {
        $userTokens = $this->entityManagerService->getRepository(UserToken::class)
            ->findBy([
                'user' => $idUser,
                'domain' => $domain
            ]);

        foreach ($userTokens as $userToken) {
            $this->entityManagerService->remove($userToken);
        }

        $this->entityManagerService->flush();
    }

    public function createRefreshToken(UserInterface $user, string $refreshToken, DomainContextEnum $domain, \DateTime $expiresAt): void
    {
        $userToken = new UserToken();

        $userToken
            ->setDomain($domain)
            ->setRefreshToken($refreshToken)
            ->setExpiresAt($expiresAt)
            ->setUser($user);

        $this->entityManagerService->sync($userToken);
    }

    public function getByRefreshToken(string $refreshToken, DomainContextEnum $domain): ?UserInterface
    {
        return $this->entityManagerService->getRepository(UserToken::class)
            ->findOneBy(
                [
                    'domain' => $domain->value,
                    'refreshToken' => $refreshToken
                ]
            )
                ?->getUser() ?? null;
    }

    public function getRefreshToken(string $idUser, DomainContextEnum $domain): UserTokenInterface|null
    {
        return $this->entityManagerService->getRepository(UserToken::class)
            ->findOneBy([
                'user' => $idUser,
                'domain' => $domain->value
            ]);
    }

    public function updateRefreshToken(string $oldToken, string $newToken, \DateTime $expiresAt): void
    {
        $userToken = $this->entityManagerService->getRepository(UserToken::class)
            ->findOneBy(['refreshToken' => $oldToken]);

        if ($userToken) {
            $userToken
                ->setRefreshToken($newToken)
                ->setExpiresAt($expiresAt);
            $this->entityManagerService->sync($userToken);
        }
    }

    public function deleteRefreshToken(string $idUser, DomainContextEnum $domain): void
    {
        $userToken = $this->getRefreshToken($idUser, $domain);

        if ($userToken) {
            $this->entityManagerService->remove($userToken);
            $this->entityManagerService->flush();
        }
    }

    public function getVerificationToken(string $token): ?UserVerifyEmail
    {
        return $this->entityManagerService->getRepository(UserVerifyEmail::class)
            ->findOneBy(['token' => $token]);
    }

    public function deleteVerificationToken(string $token): void
    {
        $verificationToken = $this->getVerificationToken($token);

        if ($verificationToken) {
            $this->entityManagerService->remove($verificationToken);
            $this->entityManagerService->flush();
        }
    }
}