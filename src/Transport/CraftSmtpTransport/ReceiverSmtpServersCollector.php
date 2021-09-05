<?php
declare(strict_types=1);

namespace MicroMailer\Transport\CraftSmtpTransport;

use MicroMailer\ValueObject\Mailbox;
use MicroMailer\ValueObject\Message;

class ReceiverSmtpServersCollector
{
    /**
     * @var array<string, string[]>
     *
     * // TODO: replace with PSR cache and implmentation, but not sure if external libraries are allowed
     */
    private array $cache = [];

    /**
     * @return string[][] Array of arrays SMTP server hostnames, $message should be delivered to
     * @example
     * [
     *      'gmail.com' => ['alt1.gmail-smtp-in.l.google.com', 'alt2.gmail-smtp-in.l.google.com'],
     *      'yahoo.com' => ['alt0.yahoo.com'],
     *      'fjakslf.com' => null, // No MX servers found
     * ]
     */
    public function collect(Message $message): array
    {
        $mailboxes = [
            ...$message->getTo(),
            ...$message->getCc(),
            ...$message->getBcc()
        ];

        $servers = [];
        $hosts = array_unique(array_map(fn(Mailbox $mailbox) => $mailbox->email()->host(), $mailboxes));
        foreach ($hosts as $host) {
            if (isset($this->cache[$host])) {
                $servers[$host] = $this->cache[$host];
                continue;
            }

            $servers[$host] = $this->cache[$host] = $this->findMxRecords($host);
        }

        return $servers;
    }

    private function findMxRecords(string $baseDomain): ?array
    {
        $hosts = [];
        if (dns_get_mx($baseDomain, $hosts) === false || $hosts === []) {
             return null;
        }

        return $hosts;
    }
}
