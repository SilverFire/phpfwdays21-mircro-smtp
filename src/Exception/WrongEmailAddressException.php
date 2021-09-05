<?php
declare(strict_types=1);

namespace MicroMailer\Exception;

class WrongEmailAddressException extends Exception
{
    public static function fromEmail(string $email): self
    {
        return new self(sprintf(
            'Email address "%s" is not valid', $email
        ));
    }
}
