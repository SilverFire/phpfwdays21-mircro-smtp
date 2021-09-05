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

        foreach ($expectedServers as $domain => $exists) {
            if ($exists) {
                $this->assertNotNull($result[$domain]);
            } else {
                $this->assertNull($result[$domain]);
            }
        }
    }

    public function messagesDataProvider()
    {
        yield [
            (new Message())
                ->withAddedTo(Mailbox::fromAddress('test@gmail.com'))
                ->withAddedTo(Mailbox::fromAddress('test2@yahoo.com'))
                ->withAddedTo(Mailbox::fromAddress('this-email@should-not-exist.ever')),
            [
                'gmail.com' => true,
                'yahoo.com' => true,
                'should-not-exist.ever' => false,
            ],
        ];
    }
}
