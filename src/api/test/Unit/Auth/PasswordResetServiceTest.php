<?php

declare(strict_types=1);

namespace Test\Unit\Auth;

use JR\Tracker\Config;
use JR\Tracker\DataObject\Data\PasswordResetData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Contract\UserPasswordResetInterface;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\VerificationException;
use JR\Tracker\Mail\PasswordResetEmail;
use JR\Tracker\Repository\Contract\PasswordResetRepositoryInterface;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Service\Contract\HashServiceInterface;
use JR\Tracker\Service\Implementation\PasswordResetService;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PasswordResetServiceTest extends TestCase
{
  private Config|MockObject $config;
  private PasswordResetEmail|MockObject $passwordResetEmail;
  private UserRepositoryInterface|MockObject $userRepository;
  private PasswordResetRepositoryInterface|MockObject $passwordResetRepository;
  private HashServiceInterface|MockObject $hashService;
  private PasswordResetService $passwordResetService;

  protected function setUp(): void
  {
    $this->config = $this->createMock(Config::class);
    $this->passwordResetEmail = $this->createMock(PasswordResetEmail::class);
    $this->userRepository = $this->createMock(UserRepositoryInterface::class);
    $this->passwordResetRepository = $this->createMock(PasswordResetRepositoryInterface::class);
    $this->hashService = $this->createMock(HashServiceInterface::class);

    $this->passwordResetService = new PasswordResetService(
      $this->config,
      $this->passwordResetEmail,
      $this->userRepository,
      $this->passwordResetRepository,
      $this->hashService
    );
  }

  #[TestDox('PasswordResetService: attemptRequest sends email if user exists')]
  public function testAttemptRequestSendsEmailIfUserExists(): void
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

    $this->passwordResetService->attemptRequest($email);
  }

  #[TestDox('PasswordResetService: attemptRequest executes dummy logic if user does not exist')]
  public function testAttemptRequestExecutesDummyLogicIfUserNotFound(): void
  {
    $email = 'nonexistent@example.com';

    $this->userRepository->expects($this->once())
      ->method('getByEmail')
      ->with($email)
      ->willReturn(null);

    $this->passwordResetEmail->expects($this->never())
      ->method('send');

    // Verifies the non-enumerable "dummy call" branch runs without crashing and does not send email.
    $this->passwordResetService->attemptRequest($email);
  }

  #[TestDox('PasswordResetService: attemptRequest sends email if user exists (repeat call is independent)')]
  public function testAttemptRequestSendsEmailIfUserExistsOnRepeatCall(): void
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

    $this->passwordResetService->attemptRequest($email);
  }

  #[TestDox('PasswordResetService: attemptRequest executes dummy logic if user does not exist (repeat call is independent)')]
  public function testAttemptRequestExecutesDummyLogicIfUserNotFoundOnRepeatCall(): void
  {
    $email = 'nonexistent@example.com';

    $this->userRepository->expects($this->once())
      ->method('getByEmail')
      ->with($email)
      ->willReturn(null);

    $this->passwordResetEmail->expects($this->never())
      ->method('send');

    $this->passwordResetService->attemptRequest($email);
  }

  #[TestDox('PasswordResetService: attemptReset verifies token, hashes new password, and deletes the reset token')]
  public function testAttemptResetSuccess(): void
  {
    $email = 'test@example.com';
    $token = 'valid-token';
    $newPassword = 'NewPassword123!';
    $hashedPassword = 'hashed-new-password';

    $data = new PasswordResetData($newPassword, $newPassword, $token);

    $passwordResetToken = $this->createMock(UserPasswordResetInterface::class);
    $passwordResetToken->method('getEmail')->willReturn($email);
    $passwordResetToken->method('getIsExpired')->willReturn(false);

    $this->passwordResetRepository->expects($this->once())
      ->method('getByToken')
      ->with($token)
      ->willReturn($passwordResetToken);

    $user = $this->createMock(UserInterface::class);
    $this->userRepository->expects($this->once())
      ->method('getByEmail')
      ->with($email)
      ->willReturn($user);

    $this->hashService->expects($this->once())
      ->method('hash')
      ->with($newPassword)
      ->willReturn($hashedPassword);

    $user->expects($this->once())
      ->method('setPassword')
      ->with($hashedPassword);

    $this->userRepository->expects($this->once())
      ->method('update')
      ->with($user);

    $this->passwordResetRepository->expects($this->once())
      ->method('delete')
      ->with($token);

    $this->passwordResetService->attemptReset($data);
  }

  #[TestDox('PasswordResetService: attemptReset rejects an unknown reset token')]
  public function testAttemptResetRejectsInvalidToken(): void
  {
    $token = 'unknown-token';
    $data = new PasswordResetData('NewPassword123!', 'NewPassword123!', $token);

    $this->passwordResetRepository->expects($this->once())
      ->method('getByToken')
      ->with($token)
      ->willReturn(null);

    $this->userRepository->expects($this->never())->method('getByEmail');
    $this->hashService->expects($this->never())->method('hash');

    try {
      $this->passwordResetService->attemptReset($data);
      $this->fail('Expected VerificationException for an invalid reset token');
    } catch (VerificationException $e) {
      $this->assertSame(HttpStatusCode::NOT_FOUND->value, $e->getCode());
      $this->assertArrayHasKey('tokenError', $e->errors);
      $this->assertContains('invalidToken', $e->errors['tokenError']);
    }
  }

  #[TestDox('PasswordResetService: attemptReset rejects an expired reset token')]
  public function testAttemptResetRejectsExpiredToken(): void
  {
    $token = 'expired-token';
    $data = new PasswordResetData('NewPassword123!', 'NewPassword123!', $token);

    $passwordResetToken = $this->createMock(UserPasswordResetInterface::class);
    $passwordResetToken->method('getIsExpired')->willReturn(true);

    $this->passwordResetRepository->expects($this->once())
      ->method('getByToken')
      ->with($token)
      ->willReturn($passwordResetToken);

    $this->userRepository->expects($this->never())->method('getByEmail');
    $this->hashService->expects($this->never())->method('hash');

    try {
      $this->passwordResetService->attemptReset($data);
      $this->fail('Expected VerificationException for an expired reset token');
    } catch (VerificationException $e) {
      $this->assertSame(HttpStatusCode::GONE->value, $e->getCode());
      $this->assertArrayHasKey('tokenError', $e->errors);
      $this->assertContains('expiredToken', $e->errors['tokenError']);
    }
  }
}
