<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\Config;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Implementation\UserPasswordReset;
use JR\Tracker\Entity\User\Implementation\UserVerifyEmail;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\VerificationException;
use JR\Tracker\Mail\PasswordResetEmail;
use JR\Tracker\Repository\Contract\PasswordResetRepositoryInterface;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Service\Contract\PasswordResetServiceInterface;

class PasswordResetService implements PasswordResetServiceInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly PasswordResetEmail $passwordResetEmail,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordResetRepositoryInterface $passwordResetRepository,

    ) {
    }

    public function attemptResetPassword(string $email): void
    {
        $user = $this->userRepository->getByEmail($email);

        if (isset($user)) {
            $this->passwordResetEmail->send($user, $this->createPasswordResetLink(...));
        } else {
            // Dummy call
            password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT, ['cost' => 4]);
        }
    }



    public function attemptResend(string $email): void
    {
        $user = $this->userRepository->getByEmail($email);

        if (!isset($user)) {
            // Dummy call
            password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT, ['cost' => 4]);
            return;
        }

        $this->passwordResetEmail->send($user, $this->createPasswordResetLink(...));
    }

    #REGION Private methods
    private function createPasswordResetLink(UserInterface $user, int $expiresHours): ?string
    {
        $email = $user->getEmail();
        $token = $this->passwordResetRepository->getActiveToken($email);

        if (isset($token)) {
            $token
                ->setToken()
                ->setExpiresAt($expiresHours)
                ->setCreatedAt();
            $this->passwordResetRepository->update($token);
        } else {
            $token = new UserPasswordReset();
            $token
                ->setEmail($email)
                ->setToken()
                ->setExpiresAt($expiresHours)
                ->setCreatedAt();

            $this->passwordResetRepository->create($token);
        }

        $baseUrl = preg_replace('/\/$/', '', $this->config->get('client_app_url'));
        $passwordResetCallbackUrl = $this->config->get('password_reset_callback_url');

        return (string) $baseUrl . $passwordResetCallbackUrl . $token->getToken();
    }
    #ENDREGION
}