<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\Builder;

use MicroMailer\Builder\MimeMessageBuilder;
use MicroMailer\ValueObject\Email;
use MicroMailer\ValueObject\Mailbox;
use MicroMailer\ValueObject\Message;
use PHPUnit\Framework\TestCase;

class MimeBuilderTest extends TestCase
{
    private string $date = 'Sun, 05 Sep 2021 09:11:18 +0000';
    private string $boundaryId = 'c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3';

    /**
     * @param Message $message
     * @param string $expectedBody
     *
     * @dataProvider messagesProvider
     */
    public function testBuilding(Message $message, string $expectedBody): void
    {
        $mock = $this->getMockBuilder(MimeMessageBuilder::class)
            ->onlyMethods(['getCurrentISODate', 'generateRandomBoundaryId'])
            ->getMock();
        $mock->method('getCurrentISODate')->willReturn($this->date);
        $mock->method('generateRandomBoundaryId')->willReturn($this->boundaryId);

        $body = $mock->build($message);
        $this->assertSame(str_replace("\n", "\r\n", $expectedBody), $body);
    }

    public function messagesProvider()
    {
        yield [
            (new Message())
                ->withFrom(new Mailbox('Dmytro Naumenko', new Email('my@silverfire.me')))
                ->withAddedTo(Mailbox::fromAddress('test@silverfire.me'))
                ->withSubject('This is a test email message')
                ->withAddedBcc(Mailbox::fromAddress('d.naumenko.a@gmail.com'))
                ->withTextBody('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.')
                ->withHtmlBody('<img src="https://prof-mk.ru/wp-content/uploads/2020/12/lorem-ipsum-tekst-ryba.png" /> Here is a Lorem Ipsum message'),
            <<<MESSAGE
Date: {$this->date}
Subject: This is a test email message
From: Dmytro Naumenko <my@silverfire.me>
To: test@silverfire.me
Bcc: d.naumenko.a@gmail.com
Content-Type: multipart/alternative; boundary="alt-c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3"

--alt-c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3
Content-Type: text/plain; charset=utf8
Content-Transfer-Encoding: base64

TG9yZW0gSXBzdW0gaXMgc2ltcGx5IGR1bW15IHRleHQgb2YgdGhlIHByaW50aW5nIGFuZCB0eXBl
c2V0dGluZyBpbmR1c3RyeS4gTG9yZW0gSXBzdW0gaGFzIGJlZW4gdGhlIGluZHVzdHJ5IHN0YW5k
YXJkIGR1bW15IHRleHQgZXZlciBzaW5jZSB0aGUgMTUwMHMsIHdoZW4gYW4gdW5rbm93biBwcmlu
dGVyIHRvb2sgYSBnYWxsZXkgb2YgdHlwZSBhbmQgc2NyYW1ibGVkIGl0IHRvIG1ha2UgYSB0eXBl
IHNwZWNpbWVuIGJvb2suIEl0IGhhcyBzdXJ2aXZlZCBub3Qgb25seSBmaXZlIGNlbnR1cmllcywg
YnV0IGFsc28gdGhlIGxlYXAgaW50byBlbGVjdHJvbmljIHR5cGVzZXR0aW5nLCByZW1haW5pbmcg
ZXNzZW50aWFsbHkgdW5jaGFuZ2VkLiBJdCB3YXMgcG9wdWxhcmlzZWQgaW4gdGhlIDE5NjBzIHdp
dGggdGhlIHJlbGVhc2Ugb2YgTGV0cmFzZXQgc2hlZXRzIGNvbnRhaW5pbmcgTG9yZW0gSXBzdW0g
cGFzc2FnZXMsIGFuZCBtb3JlIHJlY2VudGx5IHdpdGggZGVza3RvcCBwdWJsaXNoaW5nIHNvZnR3
YXJlIGxpa2UgQWxkdXMgUGFnZU1ha2VyIGluY2x1ZGluZyB2ZXJzaW9ucyBvZiBMb3JlbSBJcHN1
bS4=

--alt-c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3
Content-Type: text/html; charset=utf8
Content-Transfer-Encoding: base64

PGltZyBzcmM9Imh0dHBzOi8vcHJvZi1tay5ydS93cC1jb250ZW50L3VwbG9hZHMvMjAyMC8xMi9s
b3JlbS1pcHN1bS10ZWtzdC1yeWJhLnBuZyIgLz4gSGVyZSBpcyBhIExvcmVtIElwc3VtIG1lc3Nh
Z2U=


MESSAGE
        ];


        yield [
            (new Message())
                ->withFrom(new Mailbox('Дмитрий Науменко', new Email('my@silverfire.me')))
                ->withAddedTo(Mailbox::fromAddress('test@silverfire.me'))
                ->withSubject('Это тестовое сообщение')
                ->withTextBody('Тут ещё есть 😘 '),
            <<<MESSAGE
Date: {$this->date}
Subject: =?UTF-8?B?0K3RgtC+INGC0LXRgdGC0L7QstC+0LUg0YHQvtC+0LHRidC10L3QuNC1?=
From: =?UTF-8?B?0JTQvNC40YLRgNC40Lkg0J3QsNGD0LzQtdC90LrQvg==?= <my@silverfire.me>
To: test@silverfire.me
Content-Type: multipart/alternative; boundary="alt-c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3"

--alt-c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3
Content-Type: text/plain; charset=utf8
Content-Transfer-Encoding: base64

0KLRg9GCINC10YnRkSDQtdGB0YLRjCDwn5iYIA==


MESSAGE
        ];
    }
}
