<?php

declare(strict_types=1);

namespace Test\Unit\Auth;

use PHPUnit\Framework\TestCase;
use JR\Tracker\Mail\SignUpEmail;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use JR\Tracker\DataObject\Data\RegisterUserData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Service\Implementation\AuthService;
use JR\Tracker\Service\Contract\HashServiceInterface;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Service\Contract\TokenServiceInterface;
use JR\Tracker\Service\Contract\CookieServiceInterface;
use JR\Tracker\Service\Contract\SessionServiceInterface;
use JR\Tracker\Service\Contract\VerifyEmailServiceInterface;
use JR\Tracker\Strategy\Contract\AuthStrategyFactoryInterface;

class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private HashServiceInterface|MockObject $hashService;
    private TokenServiceInterface|MockObject $tokenService;
    private CookieServiceInterface|MockObject $cookieService;
    private AuthStrategyFactoryInterface|MockObject $authStrategyFactory;
    private SessionServiceInterface|MockObject $sessionService;
    private SignUpEmail|MockObject $signUpEmail;
    private VerifyEmailServiceInterface|MockObject $verifyEmailService;
    private AuthService $authService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->hashService = $this->createMock(HashServiceInterface::class);
        $this->tokenService = $this->createMock(TokenServiceInterface::class);
        $this->cookieService = $this->createMock(CookieServiceInterface::class);
        $this->authStrategyFactory = $this->createMock(AuthStrategyFactoryInterface::class);
        $this->sessionService = $this->createMock(SessionServiceInterface::class);
        $this->signUpEmail = $this->createMock(SignUpEmail::class);
        $this->verifyEmailService = $this->createMock(VerifyEmailServiceInterface::class);

        $this->authService = new AuthService(
            $this->userRepository,
            $this->hashService,
            $this->tokenService,
            $this->cookieService,
            $this->authStrategyFactory,
            $this->sessionService,
            $this->signUpEmail,
            $this->verifyEmailService
        );
    }

    #[TestDox('AuthService: register hashes password, creates user and sends email')]
    public function testRegisterSuccess(): void
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
            ->method('create')
            ->with($this->callback(function (RegisterUserData $passedData) use ($hashedPassword) {
                return $passedData->password === $hashedPassword;
            }))
            ->willReturn($user);

        // 3. Expect email to be sent with callable
        $this->signUpEmail->expects($this->once())
            ->method('send')
            ->with($user, $this->isInstanceOf(\Closure::class));

        $result = $this->authService->register($data);

        $this->assertSame($user, $result);
    }
}
