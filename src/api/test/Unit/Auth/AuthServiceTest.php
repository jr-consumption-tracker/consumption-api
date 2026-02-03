<?php

declare(strict_types=1);

namespace Test\Unit\Auth;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use JR\Tracker\Service\Implementation\AuthService;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Service\Contract\HashServiceInterface;
use JR\Tracker\Mail\SignUpEmail;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use PHPUnit\Framework\Attributes\TestDox;

class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private HashServiceInterface|MockObject $hashService;
    private SignUpEmail|MockObject $signUpEmail;
    private AuthService $authService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->hashService = $this->createMock(HashServiceInterface::class);
        $this->signUpEmail = $this->createMock(SignUpEmail::class);

        $this->authService = new AuthService(
            $this->userRepository,
            $this->hashService,
            $this->signUpEmail
        );
    }

    #[TestDox('AuthService: registerUser hashes password, creates user and sends email')]
    public function testRegisterUserSuccess(): void
    {
        $data = new RegisterUserData('test@example.com', 'plain-password');
        $hashedPassword = 'hashed-password';

        $user = $this->createMock(UserInterface::class);

        // 1. Expect password hashing
        $this->hashService->expects($this->once())
            ->method('hash')
            ->with('plain-password')
            ->willReturn($hashedPassword);

        // 2. Expect user creation with hashed password
        $this->userRepository->expects($this->once())
            ->method('createUser')
            ->with($this->callback(function (RegisterUserData $passedData) use ($hashedPassword) {
                return $passedData->hashedPassword === $hashedPassword;
            }))
            ->willReturn($user);

        // 3. Expect email to be sent
        $this->signUpEmail->expects($this->once())
            ->method('send')
            ->with($user);

        $result = $this->authService->registerUser($data);

        $this->assertSame($user, $result);
    }
}
