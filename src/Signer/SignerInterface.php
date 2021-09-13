<?php
declare(strict_types=1);

namespace MicroMailer\Signer;

use MicroMailer\ValueObject\Message;

/**
 * Interface SignerInterface for message signing
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
interface SignerInterface
{
    public function sign(Message $message): Message;
}
