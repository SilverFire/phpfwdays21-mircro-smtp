<?php
declare(strict_types=1);

namespace MicroMailer\ValueObject;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
final class Mailbox
{
    public function __construct(
        private string $name,
        private Email $email
    ) {
    }

    #[Pure]
    public function name(): string
    {
        return $this->name;
    }

    #[Pure]
    public function email(): Email
    {
        return $this->email;
    }

    #[Pure]
    public function __toString(): string
    {
        return sprintf('%s <%s>', $this->name, $this->email->address());
    }
}
