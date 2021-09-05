<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\Transport;

use MicroMailer\Builder\MimeMessageBuilder;
use MicroMailer\Transport\CraftSmtpTransport;
use MicroMailer\Transport\CraftSmtpTransport\ReceiverSmtpServersCollector;
use MicroMailer\Transport\SendingResult;
use MicroMailer\Transport\SmtpTransport;
use MicroMailer\ValueObject\Email;
use MicroMailer\ValueObject\Mailbox;
use MicroMailer\ValueObject\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MicroMailer\Transport\CraftSmtpTransport
 */
class CraftSmtpTransportTest extends TestCase
{
    public function testDelivery()
    {
        $transport = $this->getMockBuilder(CraftSmtpTransport::class)
            ->setConstructorArgs([new MimeMessageBuilder(), new ReceiverSmtpServersCollector()])
            ->onlyMethods(['transport'])
            ->getMock();

        $transport->method('transport')
            ->willReturnCallback(function (string $domain) {
                return new DummyTransport($domain);
            });

        $message = (new Message())
            ->withFrom(new Mailbox('Дмитрий Науменко', new Email('test@gmail.com')))
            ->withAddedTo(Mailbox::fromAddress('test2@yahoo.com'))
            ->withAddedTo(Mailbox::fromAddress('test3@gmail.com'))
            ->withAddedCc(Mailbox::fromAddress('test@failed.example.com'))
            ->withAddedCc(Mailbox::fromAddress('test@fail.com'))
            ->withSubject('Это тестовое сообщение')
            ->withTextBody('Тут ещё есть');

        $result = $transport->send($message);

        $this->assertSame([
            'test2@yahoo.com' => ['yahoo.com', 0, null],
            'test3@gmail.com' => ['gmail.com', 0, null],
            'test@failed.example.com' => ['', 10, 'No MX servers found'],
            'test@fail.com' => ['fail.com', 10, 'Just testing'],
        ], $result->perEmail);
    }
}

class DummyTransport
{
    public string $domain;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    public function send(Message $message): SendingResult
    {
        $result = (new SendingResult($message));

        if (str_contains($this->domain, 'fail')) {
            $result->log($this->domain, SendingResult::RESULT_PERMANENT_FAIL, 'Just testing');
        } else {
            $result->log($this->domain, SendingResult::RESULT_SUCCESS);
        }

        return $result;
    }
}
