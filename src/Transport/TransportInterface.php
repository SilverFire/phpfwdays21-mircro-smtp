<?php
declare(strict_types=1);

namespace MicroMailer\Transport;

use MicroMailer\ValueObject\Message;

interface TransportInterface
{
    public function connect(): void;

    public function disconnect(): void;

    public function isConnected(): bool;

    public function send(Message $message): SendingResult;

    /**
     * @param Message ...$message
     * @return SendingResult[]
     */
    public function sendBatch(Message ...$message): array;
}
