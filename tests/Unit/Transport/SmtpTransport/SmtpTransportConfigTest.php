<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\Transport\SmtpTransport;

use MicroMailer\Transport\SmtpTransport\SmtpTransportConfig;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MicroMailer\Transport\SmtpTransport\SmtpTransportConfig
 */
class SmtpTransportConfigTest extends TestCase
{
    public function testProperties()
    {
        $config = new SmtpTransportConfig();
        $this->assertSame(25, $config->getPort());

        $config = $config->withHost('mx.google.com');
        $this->assertSame('mx.google.com', $config->getHost());

        $config = $config->withDomain('gmail.com');
        $this->assertSame('gmail.com', $config->getDomain());

        $config = $config->withPort(20);
        $this->assertSame(20, $config->getPort());
    }
}
