<?php
declare(strict_types=1);

namespace MicroMailer\Transport;

class ConnectionFailedException extends TransportException
{
    public static function fromErrorString(string $server, string $error): self
    {
        return new self(sprintf('Failed to establish connection with "%s": %s', $server, $error));
    }
}
