<?php

declare(strict_types=1);

namespace JR\Tracker\Mail;

use DateTime;
use JR\Tracker\Config;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;
use JR\Tracker\Entity\User\Contract\UserInterface;
use JR\Tracker\Service\Implementation\SignedUrlService;

class SignUpEmail
{
    public function __construct(
        private readonly Config $config,
        private readonly MailerInterface $mailer,
        private readonly BodyRendererInterface $renderer,
        private readonly SignedUrlService $signedUrlService,

    ) {
    }

    public function send(UserInterface $user): void
    {
        $email = $user->getEmail();
        $expirationDate = new DateTime('+24 hours');
        $activationLink = $this->signedUrlService->fromRoute(
            'verify',
            ['uuid' => $user->getUuid(), 'hash' => sha1($email)],
            $expirationDate
        );

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