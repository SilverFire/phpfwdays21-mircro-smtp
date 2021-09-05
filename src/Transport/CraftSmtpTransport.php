<?php
declare(strict_types=1);

namespace MicroMailer\Transport;

use MicroMailer\Builder\MimeMessageBuilder;
use MicroMailer\Transport\CraftSmtpTransport\ReceiverSmtpServersCollector;
use MicroMailer\ValueObject\Message;

class CraftSmtpTransport implements TransportInterface
{
    public function __construct(
        private MimeMessageBuilder $messageBuilder,
        private ReceiverSmtpServersCollector $smtpServersCollector,
    ) {
    }

    public function connect(): void
    {
    }

    public function disconnect(): void
    {
    }

    public function isConnected(): bool
    {
    }

    public function send(Message $message): SendingResult
    {
        $targetSmtpServers = $this->smtpServersCollector->collect($message);

        $body = $this->messageBuilder->build($message);
    }

    public function sendBatch(Message ...$message): array
    {
    }
}
