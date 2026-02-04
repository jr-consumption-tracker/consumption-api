<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use League\Flysystem\Filesystem;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\RawMessage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use JR\Tracker\Service\Contract\MailerServiceInterface;

class MailerService implements MailerServiceInterface
{
    public function send(RawMessage $message, Envelope|null $envelope = null): void
    {
        $adapter = new LocalFilesystemAdapter(STORAGE_PATH . '/mail');
        $filesystem = new Filesystem($adapter);

        $filesystem->write(time() . '_' . uniqid(more_entropy: true) . '.eml', $message->toString());
    }
}