<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\Transport;

use MicroMailer\Builder\MimeMessageBuilder;
use MicroMailer\Transport\SmtpTransport;
use MicroMailer\ValueObject\Email;
use MicroMailer\ValueObject\Mailbox;
use MicroMailer\ValueObject\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MicroMailer\Transport\SmtpTransport
 */
class SmtpTransportTest extends TestCase
{
    public function testSuccessDelivery()
    {
        $config = (new SmtpTransport\SmtpTransportConfig())
            ->withHost('gmail-smtp-in.l.google.com')
            ->withDomain('silverfire.me');

        $smtp = $this->getMockBuilder(SmtpTransport::class)
            ->setConstructorArgs([$config, new MimeMessageBuilder()])
            ->onlyMethods(['sendCommand', 'connect'])
            ->getMock();

        $log = [];

        $smtp->method('sendCommand')->willReturnCallback(
            function (string $command) use (&$log) {
                $log[] = $command;

                return '';
            }
        );

        $message = (new Message())
            ->withFrom(new Mailbox('Дмитрий Науменко', new Email('my@silverfire.me')))
            ->withAddedTo(Mailbox::fromAddress('test@silverfire.me'))
            ->withSubject('Это тестовое сообщение')
            ->withTextBody('Тут ещё есть');

        $result = $smtp->send($message);

        $this->assertSame([
            0 => 'MAIL FROM: <my@silverfire.me>',
            1 => 'RCPT TO: <test@silverfire.me>',
            2 => 'DATA',
        ], array_slice($log, 0, 3));
    }
}
