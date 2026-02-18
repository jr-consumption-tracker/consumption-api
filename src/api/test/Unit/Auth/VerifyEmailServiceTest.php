<?php

declare(strict_types=1);

namespace Test\Unit\Auth;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use JR\Tracker\Service\Implementation\VerifyEmailService;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Repository\Contract\VerifyEmailRepositoryInterface;
use JR\Tracker\Mail\SignUpEmail;
use JR\Tracker\Config;
use JR\Tracker\Entity\User\Contract\UserInterface;
use PHPUnit\Framework\Attributes\TestDox;

class VerifyEmailServiceTest extends TestCase
{
  private Config|MockObject $config;
  private UserRepositoryInterface|MockObject $userRepository;
  private VerifyEmailRepositoryInterface|MockObject $verifyEmailRepository;
  private SignUpEmail|MockObject $signUpEmail;
  private VerifyEmailService $verifyEmailService;

  protected function setUp(): void
  {
    $this->config = $this->createMock(Config::class);
    $this->userRepository = $this->createMock(UserRepositoryInterface::class);
    $this->verifyEmailRepository = $this->createMock(VerifyEmailRepositoryInterface::class);
    $this->signUpEmail = $this->createMock(SignUpEmail::class);

    $this->verifyEmailService = new VerifyEmailService(
      $this->config,
      $this->userRepository,
      $this->verifyEmailRepository,
      $this->signUpEmail
    );
  }

  #[TestDox('VerifyEmailService: attemptResend sends email if user exists')]
  public function testAttemptResendSuccess(): void
  {
    $email = 'test@example.com';
    $user = $this->createMock(UserInterface::class);

    $this->userRepository->expects($this->once())
      ->method('getByEmail')
      ->with($email)
      ->willReturn($user);

    $this->signUpEmail->expects($this->once())
      ->method('send')
      ->with($user, $this->anything());

    $this->verifyEmailService->attemptResend($email);
  }

  #[TestDox('VerifyEmailService: attemptResend executes dummy logic if user does not exist')]
  public function testAttemptResendUserNotFound(): void
  {
    $email = 'nonexistent@example.com';

    $this->userRepository->expects($this->once())
      ->method('getByEmail')
      ->with($email)
      ->willReturn(null);

    $this->signUpEmail->expects($this->never())
      ->method('send');

    $this->verifyEmailService->attemptResend($email);
  }

  #[TestDox('VerifyEmailService: attemptResend executes dummy logic if user is already verified')]
  public function testAttemptResendUserAlreadyVerified(): void
  {
    $email = 'verified@example.com';
    $user = $this->createMock(UserInterface::class);
    $user->method('getEmailVerifiedAt')->willReturn(new \DateTimeImmutable());

    $this->userRepository->expects($this->once())
      ->method('getByEmail')
      ->with($email)
      ->willReturn($user);

    $this->signUpEmail->expects($this->never())
      ->method('send');

    $this->verifyEmailService->attemptResend($email);
  }
}
