<?php

declare(strict_types=1);

namespace MicroMailer\tests\Unit\Transport\CraftSmtpTransport;

use MicroMailer\Transport\CraftSmtpTransport\ReceiverSmtpServersCollector;
use MicroMailer\ValueObject\Mailbox;
use MicroMailer\ValueObject\Message;
use PHPUnit\Framework\TestCase;

class ReceiverSmtpServersCollectorTest extends TestCase
{
    /**
     * @dataProvider messagesDataProvider
     */
    public function testCollect(Message $message, array $expectedServers)
    {
        $collector = new ReceiverSmtpServersCollector();
        $result = $collector->collect($message);

        $this->assertSame($expectedServers, $result);
    }

    public function messagesDataProvider()
    {
        yield [
            (new Message())
                ->withAddedTo(Mailbox::fromAddress('test@gmail.com'))
                ->withAddedTo(Mailbox::fromAddress('test2@yahoo.com'))
                ->withAddedTo(Mailbox::fromAddress('this-email@should-not-exist.ever')),
            [
                'gmail.com' => [
                    0 => 'alt2.gmail-smtp-in.l.google.com',
                    1 => 'alt4.gmail-smtp-in.l.google.com',
                    2 => 'alt1.gmail-smtp-in.l.google.com',
                    3 => 'gmail-smtp-in.l.google.com',
                    4 => 'alt3.gmail-smtp-in.l.google.com',
                ],
                'yahoo.com' => [
                    0 => 'mta6.am0.yahoodns.net',
                    1 => 'mta7.am0.yahoodns.net',
                    2 => 'mta5.am0.yahoodns.net',
                ],
                'should-not-exist.ever' => null,
            ],
        ];
    }
}
