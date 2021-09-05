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

    /**
     * @param Mailbox $mailbox
     * @param string $expectedValue
     *
     * @dataProvider castToStringDataProvider
     */
    public function testCastingToString(Mailbox $mailbox, string $expectedValue)
    {
        $this->assertSame($expectedValue, $mailbox->__toString());
    }

    public function castToStringDataProvider()
    {
        return [
            [Mailbox::fromAddress('test@silverfire.me'), 'test@silverfire.me'],
            [new Mailbox(null, new Email('test@silverfire.me')), 'test@silverfire.me'],
            [new Mailbox('', new Email('test@silverfire.me')), 'test@silverfire.me'],
            [new Mailbox('Test Name', new Email('test@silverfire.me')), 'Test Name <test@silverfire.me>'],
        ];
    }
}
