<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\ValueObject;

use DateTimeImmutable;
use MicroMailer\ValueObject\Email;
use MicroMailer\ValueObject\Mailbox;
use MicroMailer\ValueObject\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testMessageComposing()
    {
        $message = new Message();
        $this->assertArrayNotHasKey('Message-ID', $message->getHeaders());
        $message1 = $message->withFrom(Mailbox::fromAddress('my@silverfire.me'));
        $this->assertArrayHasKey('Message-ID', $message1->getHeaders());
        $this->assertArrayHasKey('Message-ID', $message1->generateHeadersArray());

        $this->assertNotSame($message, $message1);
        $this->assertEquals(Mailbox::fromAddress('my@silverfire.me')->email(), $message1->getFrom()->email());

        $to = Mailbox::fromAddress('my+to@silverfire.me');
        $message = $message1->withAddedTo($to);
        $this->assertEquals([$to], $message->getTo());
        $this->assertEquals($message->getHeaders()['Message-ID'], $message1->getHeaders()['Message-ID']);
        $this->assertSame($message1->getMessageId(), $message1->getHeaders()['Message-ID']);

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

        $message = $message->withHeader('Test', 'Pass');
        $message = $message->withHeader('Test2', 'Pass2');
        $message = $message->withHeader('Test2', 'Pass2');
        $this->assertSame(['Message-ID' => $message->getMessageId(), 'Test' => 'Pass', 'Test2' => 'Pass2'], $message->getHeaders());
    }

    /**
     * @param Message $message
     * @param string $expectedBody
     *
     * @dataProvider messagesProvider
     */
    public function testBuilding(Message $message, string $expectedBody): void
    {
        $body = $message->build();
        $this->assertSame(str_replace("\n", "\r\n", $expectedBody), $body);
    }

    public function messagesProvider()
    {
        $date = 'Sun, 05 Sep 2021 09:11:18 +0000';
        $messageId = '<60a0511755dc@silverfire.me>';

        $message = $this->getMockBuilder(Message::class)
            ->setConstructorArgs([new DateTimeImmutable($date)])
            ->onlyMethods(['getBoundaryId'])
            ->getMock();

        $message->method('getBoundaryId')->willReturn('c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3');

        yield [
            $message
                ->withFrom(new Mailbox('Dmytro Naumenko', new Email('my@silverfire.me')))
                ->withAddedTo(Mailbox::fromAddress('test@silverfire.me'))
                ->withSubject('This is a test email message')
                ->withAddedBcc(Mailbox::fromAddress('d.naumenko.a@gmail.com'))
                ->withHeader('Message-ID', $messageId)
                ->withTextBody('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.')
                ->withHtmlBody('<img src="https://prof-mk.ru/wp-content/uploads/2020/12/lorem-ipsum-tekst-ryba.png" /> Here is a Lorem Ipsum message'),
            <<<MESSAGE
Message-ID: {$messageId}
From: Dmytro Naumenko <my@silverfire.me>
To: test@silverfire.me
Date: ${date}
Subject: This is a test email message
MIME-Version: 1.0
Content-Type: multipart/alternative; boundary=c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3

--c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable

Lorem Ipsum is simply dummy text of the printing and typesetting industry. =
Lorem Ipsum has been the industry standard dummy text ever since the 1500s,=
 when an unknown printer took a galley of type and scrambled it to make a t=
ype specimen book. It has survived not only five centuries, but also the le=
ap into electronic typesetting, remaining essentially unchanged. It was pop=
ularised in the 1960s with the release of Letraset sheets containing Lorem =
Ipsum passages, and more recently with desktop publishing software like Ald=
us PageMaker including versions of Lorem Ipsum.
--c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3
Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: quoted-printable

<img src=3D"https://prof-mk.ru/wp-content/uploads/2020/12/lorem-ipsum-tekst=
-ryba.png" /> Here is a Lorem Ipsum message
--c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3--

MESSAGE
        ];


        yield [
            $message
                ->withFrom(new Mailbox('Дмитрий Науменко', new Email('my@silverfire.me')))
                ->withAddedTo(Mailbox::fromAddress('test@silverfire.me'))
                ->withSubject('Это тестовое сообщение')
                ->withHeader('Message-ID', $messageId)
                ->withTextBody('Тут ещё есть 😘 '),
            <<<MESSAGE
Message-ID: {$messageId}
From: =?UTF-8?B?0JTQvNC40YLRgNC40Lkg0J3QsNGD0LzQtdC90LrQvg==?= <my@silverfire.me>
To: test@silverfire.me
Date: {$date}
Subject: =?UTF-8?B?0K3RgtC+INGC0LXRgdGC0L7QstC+0LUg0YHQvtC+0LHRidC10L3QuNC1?=
MIME-Version: 1.0
Content-Type: multipart/alternative; boundary=c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3

--c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable

=D0=A2=D1=83=D1=82 =D0=B5=D1=89=D1=91 =D0=B5=D1=81=D1=82=D1=8C =F0=9F=98=
=98=20
--c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3--

MESSAGE
        ];
    }
}
