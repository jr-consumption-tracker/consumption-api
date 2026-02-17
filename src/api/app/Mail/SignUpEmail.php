<?php

declare(strict_types=1);

namespace JR\Tracker\Mail;

use DateTime;
use JR\Tracker\Config;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;
use JR\Tracker\Entity\User\Contract\UserInterface;

class SignUpEmail
{
    public function __construct(
        private readonly Config $config,
        private readonly MailerInterface $mailer,
        private readonly BodyRendererInterface $renderer
    ) {
    }

    public function send(UserInterface $user, callable $linkGenerator): void
    {
        $expiresHours = 24;
        $email = $user->getEmail();
        $expirationDate = new DateTime(sprintf('+%d hours', $expiresHours));
        $activationLink = $linkGenerator($email, $expiresHours);

        if (!isset($activationLink)) {
            return;
        }

        $message = (new TemplatedEmail())
            ->from($this->config->get('mailer.from'))
            ->to($email)
            ->subject('[Spotřeba energií] Potvrďte prosím svou e-mailovou adresu')
            ->htmlTemplate('signupEmailTemplate.html.twig')
            ->context(
                [
                    'activationLink' => $activationLink,
                    'expirationDate' => $expirationDate,
                ]
            );

        $this->renderer->render($message);
        $this->mailer->send($message);
    }
}