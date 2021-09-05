<?php
declare(strict_types=1);

namespace MicroMailer\Transport;

use MicroMailer\Builder\MimeMessageBuilder;
use MicroMailer\Transport\SmtpTransport\SmtpTransportConfig;
use MicroMailer\ValueObject\Message;

class SmtpTransport implements TransportInterface
{
    private const CRLF = "\r\n";

    /**
     * @var resource
     */
    protected $socket;

    public function __construct(
        private SmtpTransportConfig $config,
        private MimeMessageBuilder $messageBuilder
    ) {
    }

    public function connect(): void
    {
        if ($this->socket !== null) {
            return;
        }

        $socket = fsockopen(
            $this->config->getHost(),
            $this->config->getPort(),
            $errorNumber,
            $errorMessage,
            $this->config->getConnectionTimeoutSeconds(),
        );

        if ($socket === false || $errorMessage !== 0) {
            throw ConnectionFailedException::fromErrorString($this->config->getHost(), $errorMessage);
        }

        $this->socket = $socket;
        $this->sendCommand('EHLO ' . $this->config->getDomain());
    }

    public function disconnect(): void
    {
        if ($this->socket === null) {
            return;
        }

        try {
            $this->sendCommand('QUIT');
            fclose($this->socket);
        } finally {
            unset($this->socket);
        }
    }

    public function isConnected(): bool
    {
        return $this->socket !== null;
    }

    public function send(Message $message): SendingResult
    {
        $this->connect();
        $result = new SendingResult($message);

        $this->sendCommand(sprintf('MAIL FROM: <%s>', $message->getFrom()->email()->address()));
        foreach ($message->getRecipients() as $recipient) {
            $this->sendCommand(sprintf('RCPT TO: <%s>', $recipient->email()->address()));
        }

        $this->sendCommand('DATA');
        $this->sendCommand($this->messageBuilder->build($message) . self::CRLF . '.');

        $result->log($this->config->getHost(), SendingResult::RESULT_SUCCESS);

        return $result;
    }

    public function sendBatch(Message ...$messages): array
    {
        return array_map([$this, 'send'], $messages);
    }

    protected function getResponse(): string
    {
        $string = '';
        stream_set_timeout($this->socket, $this->config->getConnectionTimeoutSeconds());

        while (($line = fgets($this->socket, 515)) !== false) {
            $string .= trim($line) . "\n";

            if ($line[3] === ' ') {
                break;
            }
        }

        return trim($string);
    }

    protected function sendCommand(string $command): string
    {
        fwrite($this->socket, $command . self::CRLF);

        return $this->getResponse();
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
