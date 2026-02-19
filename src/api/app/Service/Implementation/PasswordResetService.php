<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\Config;
use JR\Tracker\DataObject\Data\PasswordResetData;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Entity\User\Contract\UserPasswordResetInterface;
use JR\Tracker\Entity\User\Implementation\UserPasswordReset;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\VerificationException;
use JR\Tracker\Mail\PasswordResetEmail;
use JR\Tracker\Repository\Contract\PasswordResetRepositoryInterface;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Service\Contract\HashServiceInterface;
use JR\Tracker\Service\Contract\PasswordResetServiceInterface;

class PasswordResetService implements PasswordResetServiceInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly PasswordResetEmail $passwordResetEmail,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordResetRepositoryInterface $passwordResetRepository,
        private readonly HashServiceInterface $hashService

    ) {
    }

    public function attemptRequest(string $email): void
    {
        $user = $this->userRepository->getByEmail($email);

        if (isset($user)) {
            $this->passwordResetEmail->send($user, $this->createPasswordResetLink(...));
        } else {
            // Dummy call
            password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT, ['cost' => 4]);
        }
    }

    public function attemptReset(PasswordResetData $data): void
    {
        $passwordResetToken = $this->verifyToken($data->token);

        $user = $this->userRepository->getByEmail($passwordResetToken->getEmail());

        $this->resetPassword($user, $data->password, $data->token);
    }

    #REGION Private methods
    private function createPasswordResetLink(UserInterface $user, int $expiresHours): ?string
    {
        $email = $user->getEmail();
        $token = $this->passwordResetRepository->getByEmail($email);

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

    private function verifyToken(string $token): UserPasswordResetInterface
    {
        $passwordResetToken = $this->passwordResetRepository->getByToken($token);

        if (!isset($passwordResetToken)) {
            throw new VerificationException(['token' => ['invalidToken']], HttpStatusCode::NOT_FOUND->value);
        } else if ($passwordResetToken->getIsExpired()) {
            throw new VerificationException(['token' => ['expiredToken']], HttpStatusCode::GONE->value);
        }

        return $passwordResetToken;
    }

    private function resetPassword(UserInterface $user, string $password, string $token): void
    {
        $hashedPassword = $this->hashService->hash($password);
        $user->setPassword($hashedPassword);
        $this->userRepository->update($user);
        $this->passwordResetRepository->delete($token);
    }
    #ENDREGION
}