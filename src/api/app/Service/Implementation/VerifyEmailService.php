<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\Config;
use JR\Tracker\Entity\User\Implementation\UserVerifyEmail;
use JR\Tracker\Enum\HttpStatusCode;
use JR\Tracker\Exception\VerificationException;
use JR\Tracker\Repository\Contract\UserRepositoryInterface;
use JR\Tracker\Service\Contract\VerifyEmailServiceInterface;
use JR\Tracker\Repository\Contract\VerifyEmailRepositoryInterface;

class VerifyEmailService implements VerifyEmailServiceInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly UserRepositoryInterface $userRepository,
        private readonly VerifyEmailRepositoryInterface $verifyEmailRepository
    ) {
    }

    public function attemptVerifyEmail(string $token): void
    {
        $verificationToken = $this->verifyVerificationToken($token);
        $this->verifyEmail($verificationToken);
    }

    public function createEmailVerificationLink(string $email, int $expiresHours): ?string
    {
        $user = $this->userRepository->getByEmail($email);

        if (!isset($user)) {
            return null;
        }

        $verificationToken = $this->verifyEmailRepository->getActiveTokenByEmail($email);

        if (isset($verificationToken)) {
            $verificationToken->setExpiresAt(-24);
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

        return (string) $baseUrl . '/overeni-emailu/' . $verificationToken->getToken();
    }

    #REGION Private methods
    private function verifyVerificationToken(string $token): UserVerifyEmail
    {
        $verificationToken = $this->userRepository->getVerificationToken($token);

        if (!isset($verificationToken)) {
            throw new VerificationException(['notFound' => ['invalidToken']], HttpStatusCode::NOT_FOUND->value);
        } else if ($verificationToken->getIsExpired()) {
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