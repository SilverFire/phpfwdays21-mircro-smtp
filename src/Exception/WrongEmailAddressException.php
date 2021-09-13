<?php
declare(strict_types=1);

namespace MicroMailer\Exception;

use JetBrains\PhpStorm\Pure;

class WrongEmailAddressException extends Exception
{
    #[Pure]
    public static function fromEmail(string $email): self
    {
        return new self(sprintf(
            'Email address "%s" is not valid', $email
        ));
    }
}
