<?php

declare(strict_types=1);

namespace Test\Unit\Auth;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use JR\Tracker\Service\Implementation\PasswordResetService;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Repository\Contract\PasswordResetRepositoryInterface;
use JR\Tracker\Mail\PasswordResetEmail;
use JR\Tracker\Config;
use JR\Tracker\Entity\User\Contract\UserInterface;
use PHPUnit\Framework\Attributes\TestDox;

class PasswordResetServiceTest extends TestCase
{
  private Config|MockObject $config;
  private PasswordResetEmail|MockObject $passwordResetEmail;
  private UserRepositoryInterface|MockObject $userRepository;
  private PasswordResetRepositoryInterface|MockObject $passwordResetRepository;
  private PasswordResetService $passwordResetService;

  protected function setUp(): void
  {
    $this->config = $this->createMock(Config::class);
    $this->passwordResetEmail = $this->createMock(PasswordResetEmail::class);
    $this->userRepository = $this->createMock(UserRepositoryInterface::class);
    $this->passwordResetRepository = $this->createMock(PasswordResetRepositoryInterface::class);

    $this->passwordResetService = new PasswordResetService(
      $this->config,
      $this->passwordResetEmail,
      $this->userRepository,
      $this->passwordResetRepository
    );
  }

  #[TestDox('PasswordResetService: attemptResetPassword sends email if user exists')]
  public function testAttemptResetPasswordSuccess(): void
  {
    $email = 'test@example.com';
    $user = $this->createMock(UserInterface::class);

    $this->userRepository->expects($this->once())
      ->method('getByEmail')
      ->with($email)
      ->willReturn($user);

    $this->passwordResetEmail->expects($this->once())
      ->method('send')
      ->with($user, $this->anything());

    $this->passwordResetService->attemptResetPassword($email);
  }

  #[TestDox('PasswordResetService: attemptResetPassword executes dummy logic if user does not exist')]
  public function testAttemptResetPasswordUserNotFound(): void
  {
    $email = 'nonexistent@example.com';

    $this->userRepository->expects($this->once())
      ->method('getByEmail')
      ->with($email)
      ->willReturn(null);

    $this->passwordResetEmail->expects($this->never())
      ->method('send');

    // Capture start time to potentially verify "non-blocking" nature if we wanted, 
    // but here we just verify it doesn't crash and calls the mock correctly.
    $this->passwordResetService->attemptResetPassword($email);
  }

  #[TestDox('PasswordResetService: attemptResend sends email if user exists')]
  public function testAttemptResendSuccess(): void
  {
    $email = 'test@example.com';
    $user = $this->createMock(UserInterface::class);

    $this->userRepository->expects($this->once())
      ->method('getByEmail')
      ->with($email)
      ->willReturn($user);

    $this->passwordResetEmail->expects($this->once())
      ->method('send')
      ->with($user, $this->anything());

    $this->passwordResetService->attemptResend($email);
  }

  #[TestDox('PasswordResetService: attemptResend executes dummy logic if user does not exist')]
  public function testAttemptResendUserNotFound(): void
  {
    $email = 'nonexistent@example.com';

    $this->userRepository->expects($this->once())
      ->method('getByEmail')
      ->with($email)
      ->willReturn(null);

    $this->passwordResetEmail->expects($this->never())
      ->method('send');

    $this->passwordResetService->attemptResend($email);
  }
}
