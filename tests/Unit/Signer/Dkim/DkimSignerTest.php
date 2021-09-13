<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\Signer\Dkim;

use DateTimeImmutable;
use MicroMailer\Signer\Dkim\DkimException;
use MicroMailer\Signer\Dkim\DkimOptions;
use MicroMailer\Signer\Dkim\DkimSigner;
use MicroMailer\ValueObject\Email;
use MicroMailer\ValueObject\Mailbox;
use MicroMailer\ValueObject\Message;
use PHPUnit\Framework\TestCase;

class DkimSignerTest extends TestCase
{
    protected string $privateKey = <<<RSA
-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQDrpXPQY9dsI0wbt6DPDq8LED6KkHmJNBTo4qil0heUlOYLxG7g
iWso3Gssx1o+xDvgubMQknGS7xWfCg8R847frGyj9WVZNsC9wggQs2y5uxNlqhGu
1xWi9jaKoYe4nbFbC10/t7ANmw+f3NzuGWGdldpXdNl8Fz4wYukQX2mCMwIDAQAB
AoGABqULtvyZvnrgUofDCROo6+7xVIbuZmgJjueVSde6wn3QXtSTK9G0K9rLSt/0
M4DlD1ktK3J1sWb8fReThTYQ+T/QBzllf2EBVDOcsA81n92ShuqxkXhTErJ1Gg7o
gkx3dLq1Q7AHehOWxfrGANE0MFzaBaVYeYQmbHgXGdbOksECQQD8kNiQJAGPy2/L
Makq4fgeKBHnEwdnySUVjE0P4MZr6iZJcOuTYIDrx/i3u6bJsz2HLsebx/84AS17
dwqK9AKNAkEA7tm2JFL3capVCY3cdyGaYdoU+la8OpU3SxUc3/sBBTTBM8tN3g3f
YHpp+nc5A6DcCMx2ckOD/Ssxp8gycuDHvwJBAMFbLsCjICLy70JTYZx35NlJefM6
6Td2kZJ+l9ypC59AYlFlRqTMg8Z+kJYw7k6Kj3c3xA8qPOSmWiikiQi6KF0CQQDc
0UA4Us/bMPtPSuzQ4qsk3gbY7kk06/DjpFA+roLh+kTICqQhr7edLW8/FOwL87KZ
G9ZBizPCYiWVFVu2oyNnAkBQbtRU2s3OUcersMU6XtDrjXgWp5mX1hA65K2TwEeZ
BryVrtcyT/EEgFdKjcFnLVrln3zeU/ERBqYMKrccqDOX
-----END RSA PRIVATE KEY-----
RSA;

    protected string $selector = 'silverfire';
    protected string $domain = 'testdkim.silverfire.me';
    protected ?int $timestamp = null;

    protected DkimSigner $signer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signer = $this->getMockBuilder(DkimSigner::class)
            ->setConstructorArgs([$this->privateKey, $this->selector, $this->domain, new DkimOptions()])
            ->onlyMethods(['getUnixTime'])
            ->getMock();

        $this->signer->method('getUnixTime')->willReturnCallback(fn() => $this->timestamp ?? time());
    }

    /**
     * @param Message $message
     * @throws DkimException
     *
     * @dataProvider messageProvider
     */
    public function testSignature(int $timestamp, Message $message, string $expectedSignature)
    {
        $this->timestamp = $timestamp;
        $signedMessage = $this->signer->sign($message);

        $this->assertArrayHasKey('DKIM-Signature', $signedMessage->getHeaders());
        $signature = $signedMessage->getHeaders()['DKIM-Signature'];
        $this->assertSame($expectedSignature, $signature);
    }

    public function messageProvider()
    {
        $message = $this->getMockBuilder(Message::class)
                        ->setConstructorArgs([new DateTimeImmutable('Sun, 05 Sep 2021 09:11:18 +0000')])
                        ->onlyMethods(['getBoundaryId'])
                        ->getMock();

        $message->method('getBoundaryId')->willReturn('c3bc5928dfd6197a760d660f48b68d34a118f8bf333ab7aeb85954beb996b5b3');

        yield [
            1631349571,
            $message
                ->withFrom(new Mailbox('Dmytro Naumenko', new Email('my@testdkim.silverfire.me')))
                ->withAddedTo(Mailbox::fromAddress('test@silverfire.me'))
                ->withSubject('This is a test email message')
                ->withAddedBcc(Mailbox::fromAddress('d.naumenko.a@gmail.com'))
                ->withHeader('Message-ID', '123123@testdkim.silverfire.me')
                ->withTextBody('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.')
                ->withHtmlBody('<img src="https://prof-mk.ru/wp-content/uploads/2020/12/lorem-ipsum-tekst-ryba.png" /> Here is a Lorem Ipsum message'),
            'v=1; q=dns/txt; a=rsa-sha256; bh=QQrsSoBvf+a0TILLNJ2xCS/G4DCckT3Y9N5XGaJYrtM=; d=silverfire; h=Message-ID: From: To: Date: Subject: MIME-Version; i=@silverfire; s=testdkim.silverfire.me; t=1631349571; c=relaxed/relaxed; b=YAJF3GB476GHbO0WS5p4vEo5+r0fRqpgiTOmKwLBWe/HjReFqKgMt/8CgMKTlNFCnNgTTtL7bi3Je2j1aSA2hbcE3x42MgaV+o9GCAX0O831bMMCRSFJPhdAqAbaJWXw+YrJL037cap+jUofo/zvlKIZC+dYvwwDsTR/cZyq+TQ='
        ];
    }
}
