<?php
declare(strict_types=1);

namespace MicroMailer\Transport;

use JetBrains\PhpStorm\Immutable;
use MicroMailer\Exception\Exception;
use MicroMailer\ValueObject\Email;
use MicroMailer\ValueObject\Message;

final class SendingResult
{
    public const RESULT_SUCCESS = 0;
    public const RESULT_TEMP_FAIL = 1;
    public const RESULT_PERMANENT_FAIL = 10;

    #[Immutable]
    public Message $message;
    /**
     * @psalm-readonly-allow-private-mutation
     * @example
     * [
     *     'test@gmail.com' => ['mx1.gmail.com', self::RESULT_PERMANENT_FAIL, 'The recipient does not exist'],
     *     'test2@gmail.com' => ['mx2.gmail.com', self::RESULT_SUCCESS, null],
     * ]
     */
    public array $perEmail = [];

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function mergeFrom(self $otherResult): self
    {
        if ($otherResult->message !== $this->message) {
            throw new Exception('Merging sending results for different messages does not make sense');
        }

        foreach ($otherResult->perEmail as $email => $result) {
            $this->perEmail[$email] = $result;
        }

        return $this;
    }

    public function mergeFromOtherResultForDomain(self $otherResult, string $domain): self
    {
        if ($otherResult->message !== $this->message) {
            throw new Exception('Merging sending results for different messages does not make sense');
        }

        foreach ($otherResult->perEmail as $email => $result) {
            if ((new Email($email))->host() === $domain) {
                $this->perEmail[$email] = $result;
            }
        }

        return $this;
    }

    /**
     * @param Email $email
     * @param string $host
     * @param self::RESULT_* $result
     * @param string|null $log
     */
    public function logPartial(Email $email, string $host, int $result, ?string $log = null): void
    {
        $this->perEmail[$email->__toString()] = [$host, $result, $log];
    }

    /**
     * @param self::RESULT_* $result
     * @param string|null $log
     */
    public function log(string $host, int $result, ?string $log = null): void
    {
        foreach ($this->message->getRecipients() as $recipient) {
            $this->logPartial($recipient->email(), $host, $result, $log);
        }
    }

    public function logRecipientsByDomain(string $domain, string $host, int $result, ?string $log = null): void
    {
        foreach ($this->message->getRecipients() as $recipient) {
            if ($recipient->email()->host() === $domain) {
                $this->logPartial($recipient->email(), $host, $result, $log);
            }
        }
    }
}
