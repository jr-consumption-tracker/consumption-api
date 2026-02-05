<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use DateTime;
use JR\Tracker\Config;
use JR\Tracker\Entity\User\Implementation\UserVerifyEmail;
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

    public function createEmailVerificationLink(string $email, int $expiresHours): ?string
    {
        $user = $this->userRepository->getUserByEmail($email);

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
}