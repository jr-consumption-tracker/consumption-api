<?php

declare(strict_types=1);

namespace JR\Tracker\Repository\Implementation;

use JR\Tracker\Enum\UserRoleTypeEnum;
use JR\Tracker\Entity\User\Implementation\User;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Implementation\UserInfo;
use JR\Tracker\Entity\User\Implementation\UserRole;
use JR\Tracker\Entity\User\Implementation\UserRoleType;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Service\Contract\EntityManagerServiceInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManagerService,
    ) {

    }

    public function createUser(RegisterUserData $data): UserInterface
    {
        $user = new User();

        $this->entityManagerService->wrapInTransaction(function () use ($user, $data) {
            // Insert user
            $user
                ->setUuid()
                ->setEmail($data->email)
                ->setPassword($data->hashedPassword);

            $idUser = $this->entityManagerService->sync($user);

            // Insert user info
            $userInfo = new UserInfo();

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
}