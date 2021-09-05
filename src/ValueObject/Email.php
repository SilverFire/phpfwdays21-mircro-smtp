<?php
declare(strict_types=1);

namespace MicroMailer\ValueObject;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use MicroMailer\Exception\WrongEmailAddressException;

#[Immutable]
class Email
{
    private string $address;

    public function __construct(string $address)
    {
        $address = $this->convertToAscii($address);

        $filteredAddress = filter_var($address, FILTER_VALIDATE_EMAIL);
        if ($filteredAddress === false) {
            throw WrongEmailAddressException::fromEmail($address);
        }

        $this->address = $filteredAddress;
    }

    #[Pure]
    public function __toString(): string
    {
        return $this->address;
    }

    #[Pure]
    public function address(): string
    {
        return $this->address;
    }

    #[Pure]
    private function convertToAscii(string $address): string
    {
        [$left, $right] = explode('@', $address, 2);

        return
            idn_to_ascii($left, IDNA_NONTRANSITIONAL_TO_ASCII,INTL_IDNA_VARIANT_UTS46)
            . '@' .
            idn_to_ascii($right, IDNA_NONTRANSITIONAL_TO_ASCII,INTL_IDNA_VARIANT_UTS46);
    }
}
