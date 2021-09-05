<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\ValueObject;

use MicroMailer\Exception\WrongEmailAddressException;
use MicroMailer\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    /**
     * @param string $address
     *
     * @dataProvider validAddressDataProvider
     * @covers \MicroMailer\ValueObject\Email
     */
    public function testValid(string $address, string $expected = null): void
    {
        $expected ??= $address;

        $email = new Email($address);
        $this->assertSame($expected, $email->address());
        $this->assertSame($expected, $email->__toString());
    }

    public function validAddressDataProvider(): array
    {
        return [
            ['test@example.com'],
            ['test+1238fna@example.com'],
            ['пошта@емайл.укр', 'xn--80a1acn3a@xn--80ajnic.xn--j1amh']
        ];
    }
    /**
     * @param string $address
     *
     * @dataProvider invalidAddressDataProvider
     * @covers \MicroMailer\ValueObject\Email
     */
    public function testInalid(string $address): void
    {
        $this->expectException(WrongEmailAddressException::class);
        $this->expectExceptionMessage("Email address \"$address\" is not valid");

        new Email($address);
    }

    public function invalidAddressDataProvider(): array
    {
        return [
            ['test @example.com'],
        ];
    }
}
