<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\Config;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Implementation\UserVerifyEmail;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\VerificationException;
use JR\Tracker\Mail\SignUpEmail;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Repository\Contract\VerifyEmailRepositoryInterface;
use JR\Tracker\Service\Contract\VerifyEmailServiceInterface;

class VerifyEmailService implements VerifyEmailServiceInterface
{
  public function __construct(
    private readonly Config $config,
    private readonly UserRepositoryInterface $userRepository,
    private readonly VerifyEmailRepositoryInterface $verifyEmailRepository,
    private readonly SignUpEmail $signUpEmail,
  ) {
  }

  public function attemptVerify(string $token): void
  {
    $verificationToken = $this->verifyToken($token);
    $this->verifyEmail($verificationToken);
  }

  public function createLink(UserInterface $user, int $expiresHours): string
  {
    $email = $user->getEmail();
    $verificationToken = $this->verifyEmailRepository->getActiveTokenByEmail($email);

    if (isset($verificationToken)) {
      $verificationToken
        ->setToken()
        ->setExpiresAt($expiresHours)
        ->setCreatedAt();
      $this->verifyEmailRepository->updateVerifyEmail($verificationToken);
    } else {
      $verificationToken = new UserVerifyEmail();
      $verificationToken
        ->setEmail($email)
        ->setToken()
        ->setExpiresAt($expiresHours)
        ->setCreatedAt();

      $this->verifyEmailRepository->createVerifyEmail($verificationToken);
    }

    $baseUrl = preg_replace('/\/$/', '', $this->config->get('client_app_url'));
    $verifyEmailCallbackUrl = $this->config->get('verify_email_callback_url');

    return (string) $baseUrl . $verifyEmailCallbackUrl . $verificationToken->getToken();
  }

  public function attemptResend(string $email): void
  {
    $user = $this->userRepository->getByEmail($email);

    if (!isset($user) || $user->getEmailVerifiedAt() !== null) {
      // Dummy call
      password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT, ['cost' => 4]);

      return;
    }

    $this->signUpEmail->send($user, $this->createLink(...));
  }

  #REGION Private methods
  private function verifyToken(string $token): UserVerifyEmail
  {
    $verificationToken = $this->userRepository->getVerificationToken($token);

    if (!isset($verificationToken)) {
      throw new VerificationException(['notFound' => ['invalidToken']], HttpStatusCode::NOT_FOUND->value);
    } elseif ($verificationToken->getIsExpired()) {
      throw new VerificationException(['gone' => ['expiredToken']], HttpStatusCode::GONE->value);
    }

    return $verificationToken;
  }

  private function verifyEmail(UserVerifyEmail $verifyEmail): void
  {
    $this->userRepository->deleteVerificationToken($verifyEmail->getToken());

    $user = $this->userRepository->getByEmail($verifyEmail->getEmail());
    $user->setEmailVerifiedAt();

    $this->userRepository->update($user);
  }
  #ENDREGION
}
