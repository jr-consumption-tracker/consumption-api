<?php

declare(strict_types=1);

namespace JR\Tracker\Mail;

use DateTime;
use JR\Tracker\Config;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;
use JR\Tracker\Entity\User\Contract\UserInterface;

class PasswordResetEmail
{
    public function __construct(
        private readonly Config $config,
        private readonly MailerInterface $mailer,
        private readonly BodyRendererInterface $renderer
    ) {
    }

    public function send(UserInterface $user, callable $linkGenerator): void
    {
        $appName = $this->config->get('app_name');

        $expiresHours = 1;
        $email = $user->getEmail();
        $expirationDate = new DateTime(sprintf('+%d hours', $expiresHours), new \DateTimeZone('Europe/Prague'));
        $activationLink = $linkGenerator($user, $expiresHours);

        if (!isset($activationLink)) {
            return;
        }

        $message = (new TemplatedEmail())
            ->from($this->config->get('mailer.from'))
            ->to($email)
            ->subject("[$appName] Odkaz pro obnovení hesla")
            ->htmlTemplate('passwordResetEmailTemplate.html.twig')
            ->context(
                [
                    'appName' => $appName,
                    'resetLink' => $activationLink,
                    'expirationDate' => $expirationDate->format('d. m. Y H:i'),
                ]
            );

        $this->renderer->render($message);
        $this->mailer->send($message);
    }
}