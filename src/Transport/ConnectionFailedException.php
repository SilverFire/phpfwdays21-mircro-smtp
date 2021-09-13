<?php
declare(strict_types=1);

namespace MicroMailer\Transport;

use JetBrains\PhpStorm\Pure;

class ConnectionFailedException extends TransportException
{
    #[Pure]
    public static function fromErrorString(string $server, string $error): self
    {
        return new self(sprintf('Failed to establish connection with "%s": %s', $server, $error));
    }
}
