<?php
declare(strict_types=1);

namespace MicroMailer\ValueObject;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
final class Mailbox
{
    public function __construct(
        private ?string $name,
        private Email $email
    ) {
    }

    #[Pure]
    public function name(): ?string
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
        if (!empty($this->name)) {
            return sprintf('%s <%s>', $this->name, $this->email->address());
        }

        return $this->email()->address();
    }

    #[Pure]
    public static function fromAddress(string $address): self
    {
        return new self(null, new Email($address));
    }

    #[Pure]
    public static function fromString(string $address): self
    {
        if (str_contains($address, '<')) {
            preg_match('/^([^<]+)<([^>]+)>$/', $address, $matches);

            return new self(trim($matches[1]), new Email($matches[2]));
        }

        return new self(null, new Email($address));
    }

    #[Pure]
    public function mimeEncoded(): string
    {
        if (!empty($this->name)) {
            $encodedName = mb_encode_mimeheader($this->name, 'UTF-8', 'B');
            return sprintf('%s <%s>', $encodedName, $this->email->address());
        }

        return $this->email()->address();
    }
}
