<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\Transport;

use MicroMailer\Exception\Exception;
use MicroMailer\ValueObject\Email;
use MicroMailer\ValueObject\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MicroMailer\Transport\SendingResult
 */
class SendingResultTest extends TestCase
{
    public function testSuccessMerge(): void
    {
        $message = new Message();

        $result = new SendingResult($message);
        $result->logPartial(new Email('test@example.com'), 'mx.example.com', SendingResult::RESULT_SUCCESS);
        $this->assertSame(['test@example.com' => ['mx.example.com', SendingResult::RESULT_SUCCESS, null]], $result->perEmail);

        $result1 = new SendingResult($message);
        $result1->logPartial(new Email('test2@example.com'), 'mx.example.com', SendingResult::RESULT_TEMP_FAIL, 'Try in 1 hour');
        $this->assertSame(['test2@example.com' => ['mx.example.com', SendingResult::RESULT_TEMP_FAIL, 'Try in 1 hour']], $result1->perEmail);

        $result->mergeFrom($result1);
        $this->assertSame([
            'test@example.com' => ['mx.example.com', SendingResult::RESULT_SUCCESS, null],
            'test2@example.com' => ['mx.example.com', SendingResult::RESULT_TEMP_FAIL, 'Try in 1 hour']
        ], $result->perEmail);
    }

    public function testSuccessMergeByDomain(): void
    {
        $message = new Message();

        $result = new SendingResult($message);
        $result->logPartial(new Email('test@example.com'), 'mx.example.com', SendingResult::RESULT_SUCCESS);
        $this->assertSame(['test@example.com' => ['mx.example.com', SendingResult::RESULT_SUCCESS, null]], $result->perEmail);

        $result1 = new SendingResult($message);
        $result1->logPartial(new Email('test2@foobar.com'), 'mx.foobar.com', SendingResult::RESULT_TEMP_FAIL, 'Try in 1 hour');
        $result1->logPartial(new Email('test2@example.com'), 'mx.example.com', SendingResult::RESULT_TEMP_FAIL, 'Try in 2 hours');
        $this->assertSame([
            'test2@foobar.com' => ['mx.foobar.com', SendingResult::RESULT_TEMP_FAIL, 'Try in 1 hour'],
            'test2@example.com' => ['mx.example.com', SendingResult::RESULT_TEMP_FAIL, 'Try in 2 hours']
        ], $result1->perEmail);

        $result->mergeFromOtherResultForDomain($result1, 'foobar.com');
        $this->assertSame([
            'test@example.com' => ['mx.example.com', SendingResult::RESULT_SUCCESS, null],
            'test2@foobar.com' => ['mx.foobar.com', SendingResult::RESULT_TEMP_FAIL, 'Try in 1 hour']
        ], $result->perEmail);
    }

    public function testFailedMerge(): void
    {
        $result = new SendingResult(new Message());
        $result2 = new SendingResult(new Message());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Merging sending results for different messages does not make sense');

        $result->mergeFrom($result2);
    }
}
