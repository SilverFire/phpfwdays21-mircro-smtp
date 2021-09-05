<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\ValueObject;

use MicroMailer\ValueObject\Email;
use MicroMailer\ValueObject\Mailbox;
use PHPUnit\Framework\TestCase;

class MailboxTest extends TestCase
{
    public function testCreation()
    {
        $name = 'Dmytro Naumenko';
        $email = new Email('my@silverfire.me');

        $mailbox = new Mailbox($name, $email);
        $this->assertSame($name, $mailbox->name());
        $this->assertSame($email, $mailbox->email());
    }

    public function testCreationWithNamedConstructor(): void
    {
        $mailbox = Mailbox::fromAddress('my@silverfire.me');
        $this->assertNull($mailbox->name());
        $this->assertSame('my@silverfire.me', $mailbox->email()->address());
    }
}
