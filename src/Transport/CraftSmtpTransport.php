<?php
declare(strict_types=1);

namespace MicroMailer\Transport;

use MicroMailer\Builder\MimeMessageBuilder;
use MicroMailer\Transport\CraftSmtpTransport\ReceiverSmtpServersCollector;
use MicroMailer\Transport\SmtpTransport\SmtpTransportConfig;
use MicroMailer\ValueObject\Message;

class CraftSmtpTransport implements TransportInterface
{
    public function __construct(
        private MimeMessageBuilder $messageBuilder,
        private ReceiverSmtpServersCollector $smtpServersCollector,
    ) {
    }

    public function connect(): void
    {
    }

    public function disconnect(): void
    {
        foreach ($this->_transports as $key => $transport) {
            $transport->disconnect();
            unset($transport[$key]);
        }
    }

    public function isConnected(): bool
    {
        return !empty($this->_transports);
    }

    public function send(Message $message): SendingResult
    {
        $targetSmtpServers = $this->smtpServersCollector->collect($message);

        $result = new SendingResult($message);
        foreach ($targetSmtpServers as $domain => $hosts) {
            if ($hosts === null) {
                // TODO: mark undeliverable
                $result->logRecipientsByDomain($domain, '', SendingResult::RESULT_PERMANENT_FAIL, 'No MX servers found');
                continue;
            }

            $result->mergeFromOtherResultForDomain(
                $this->sendViaOneOfHosts($message, $domain, $hosts),
                $domain
            );
        }

        return $result;
    }

    private function sendViaOneOfHosts(Message $message, string $domain, array $hosts): SendingResult
    {
        foreach ($hosts as $host) {
            try {
                return $this->transport($domain, $host)->send($message);
            } catch (ConnectionFailedException $e) {
            }
        }

        $result = new SendingResult($message);
        $result->logRecipientsByDomain($domain, $host, SendingResult::RESULT_TEMP_FAIL, $e->getMessage());

        return $result;
    }

    /** @var SmtpTransport[] */
    private array $_transports = [];
    protected function transport(string $domain, string $host)
    {
        if (!isset($this->_transports[$domain])) {
            $this->_transports[$domain] = new SmtpTransport(
                (new SmtpTransportConfig())
                    ->withHost($host)
                    ->withDomain($domain),
                $this->messageBuilder
            );
        }

        return $this->_transports[$domain];
    }

    public function sendBatch(Message ...$messages): array
    {
        return array_map([$this, 'send'], $messages);
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
