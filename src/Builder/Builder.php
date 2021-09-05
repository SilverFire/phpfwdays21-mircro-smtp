<?php
declare(strict_types=1);

namespace MicroMailer\Builder;

use JetBrains\PhpStorm\Pure;
use MicroMailer\ValueObject\Message;

class Builder implements BuilderInterface
{
    private const CRLF = "\r\n";
    protected const CHARSET_UTF8 = 'utf8';
    private string $charset = self::CHARSET_UTF8;

    public function build(Message $message): string
    {
        $body = '';
        $boundaryId = $this->generateRandomBoundaryId();

        $headers = $this->generateHeadersArray($message);
        $headers['Content-Type'] = 'multipart/alternative; boundary="alt-' . $boundaryId . '"';
        foreach ($headers as $name => $value) {
            $body .= sprintf('%s: %s%s', $name, $value, self::CRLF);
        }

        $body .= self::CRLF;

        if ($message->getTextBody() !== null) {
            $body .= '--alt-' . $boundaryId . self::CRLF;
            $body .= 'Content-Type: text/plain; charset=' . $this->charset . self::CRLF;
            $body .= 'Content-Transfer-Encoding: base64' . self::CRLF . self::CRLF;
            $body .= chunk_split(base64_encode($message->getTextBody())) . self::CRLF;
        }

        if ($message->getHtmlBody() !== null) {
            $body .= '--alt-' . $boundaryId . self::CRLF;
            $body .= 'Content-Type: text/html; charset=' . $this->charset . self::CRLF;
            $body .= 'Content-Transfer-Encoding: base64' . self::CRLF . self::CRLF;
            $body .= chunk_split(base64_encode($message->getHtmlBody())) . self::CRLF;
        }

        // TODO: implement attachments

        return $body;
    }

    /**
     * @param Message $message
     * @return array The array of sanitized headers
     */
    private function generateHeadersArray(Message $message): array
    {
        $headers = [];
        $headers['Date'] = $this->getCurrentISODate();
        $headers['Subject'] = $this->sanitizeHeader($message->getSubject());
        $headers['From'] = $message->getFrom();
        $headers['To'] = implode(', ', $message->getTo());
        if ($message->getCc() !== []) {
            $headers['Cc'] = implode(', ', $message->getCc());
        }
        if ($message->getBcc() !== []) {
            $headers['Bcc'] = implode(', ', $message->getBcc());
        }

        foreach ($message->getHeaders() as $name => $value) {
            if (!isset($headers[$name])) {
                $headers[$name] = $this->sanitizeHeader($value);
            }
        }

        return $headers;
    }

    private function sanitizeHeader(string $value): string
    {
        return trim(str_replace(["\r", "\n"], '', $value));
    }

    /**
     * TODO: Extract to BoundaryID Generator?
     *
     * @return string
     * @throws \Exception
     */
    #[Pure]
    protected function generateRandomBoundaryId(): string
    {
        $length = 32;
        $bytes = random_bytes($length);

        return hash('sha256', $bytes);
    }

    #[Pure]
    protected function getCurrentISODate(): string
    {
        return date('r');
    }
}
