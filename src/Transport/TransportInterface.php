<?php
declare(strict_types=1);

namespace MicroMailer\Transport;

use MicroMailer\ValueObject\Message;

interface TransportInterface
{
    /**
     * @throws ConnectionFailedException
     */
    public function connect(): void;

    public function disconnect(): void;

    public function isConnected(): bool;

    public function send(Message $message): SendingResult;

    /**
     * @param Message ...$messages
     * @return SendingResult[]
     */
    public function sendBatch(Message ...$messages): array;
}
