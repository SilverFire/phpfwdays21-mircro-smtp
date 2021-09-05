<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\ValueObject;

use MicroMailer\ValueObject\Mailbox;
use MicroMailer\ValueObject\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testMessageComposing()
    {
        $message = new Message();
        $message1 = $message->withFrom(Mailbox::fromAddress('my@silverfire.me'));

        $this->assertNotSame($message, $message1);
        $this->assertEquals(Mailbox::fromAddress('my@silverfire.me')->email(), $message1->getFrom()->email());

        $to = Mailbox::fromAddress('my+to@silverfire.me');
        $message = $message1->withAddedTo($to);
        $this->assertEquals([$to], $message->getTo());

        $to2 = Mailbox::fromAddress('my+to2@silverfire.me');
        $message = $message->withAddedTo($to2);
        $this->assertEquals([$to, $to2], $message->getTo());

        $message = $message->withAddedCc($to)->withAddedCc($to2);
        $this->assertEquals([$to, $to2], $message->getCc());

        $message = $message->withAddedBcc($to2)->withAddedBcc($to);
        $this->assertEquals([$to2, $to], $message->getBcc());

        $html = '<b>TEST</b>';
        $message = $message->withHtmlBody($html);
        $this->assertSame($html, $message->getHtmlBody());

        $text = 'TEST';
        $message = $message->withTextBody($text);
        $this->assertSame($text, $message->getTextBody());

        $subject = 'subj123';
        $message = $message->withSubject($subject);
        $this->assertSame($subject, $message->getSubject());

        $message = $message->withAddedHeader('Test', 'Pass');
        $message = $message->withAddedHeader('Test2', 'Pass2');
        $message = $message->withAddedHeader('Test2', 'Pass2');
        $this->assertSame(['Test' => 'Pass', 'Test2' => 'Pass2'], $message->getHeaders());
    }
}
