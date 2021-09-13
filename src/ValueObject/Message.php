<?php
declare(strict_types=1);

namespace MicroMailer\ValueObject;

use BadMethodCallException;
use DateTimeImmutable;
use JetBrains\PhpStorm\Pure;

use function ord;

class Message
{
    public const CRLF = "\r\n";
    private const CHARSET_UTF8 = 'utf-8';

    private string $date;
    private string $boundaryId;
    private string $charset = self::CHARSET_UTF8;
    private ?Mailbox $from = null;
    /**
     * @var list<Mailbox>
     */
    private array $to = [];
    /**
     * @var list<Mailbox>
     */
    private array $cc = [];
    /**
     * @var list<Mailbox>
     */
    private array $bcc = [];

    private ?string $textBody = null;
    private ?string $htmlBody = null;
    /**
     * @var array<string, string>
     */
    protected array $headers = [];
    private ?string $subject = null;

    #[Pure]
    public function __construct(
        ?DateTimeImmutable $date = null
    ) {
        $this->boundaryId = $this->generateRandomBoundaryId();

        $this->date = ($date ?? new DateTimeImmutable())->format('r');
    }

    #[Pure]
    protected function generateRandomBoundaryId(): string
    {
        $length = 32;
        $bytes = random_bytes($length);

        return substr(hash('sha256', $bytes), 0, 8);
    }

    /**
     * @return Mailbox|null
     */
    public function getFrom(): ?Mailbox
    {
        return $this->from;
    }

    /**
     * @return list<Mailbox>
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @return list<Mailbox>
     */
    #[Pure]
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @return list<Mailbox>
     */
    #[Pure]
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * @return list<Mailbox>
     */
    #[Pure]
    public function getRecipients(): array
    {
        return [...$this->getTo(), ...$this->getCc(), ...$this->getBcc()];
    }

    #[Pure]
    public function withFrom(Mailbox $from): self
    {
        $self = clone $this;
        $self->from = $from;
        $self->getMessageId();

        return $self;
    }

    #[Pure]
    public function withAddedTo(Mailbox $to): self
    {
        $self = clone $this;
        $self->to[] = $to;

        return $self;
    }

    #[Pure]
    public function withAddedCc(Mailbox $cc): self
    {
        $self = clone $this;
        $self->cc[] = $cc;

        return $self;
    }

    #[Pure]
    public function withAddedBcc(Mailbox $bcc): self
    {
        $self = clone $this;
        $self->bcc[] = $bcc;

        return $self;
    }

    #[Pure]
    public function withTextBody(string $text): self
    {
        $self = clone $this;
        $self->textBody = $text;

        return $self;
    }

    #[Pure]
    public function withHtmlBody(string $html): self
    {
        $self = clone $this;
        $self->htmlBody = $html;

        return $self;
    }

    #[Pure]
    public function getTextBody(): ?string
    {
        return $this->textBody;
    }

    #[Pure]
    public function getHtmlBody(): ?string
    {
        return $this->htmlBody;
    }

    #[Pure]
    public function withHeader(string $name, string $value): self
    {
        $self = clone $this;
        $self->headers[$name] = $value;

        return $self;
    }

    #[Pure]
    public function withSubject(string $subject): self
    {
        $self = clone $this;
        $self->subject = $subject;

        return $self;
    }

    /**
     * @return string[]
     * @psalm-return array<string, string>
     */
    #[Pure]
    public function getHeaders(): array
    {
        return $this->headers;
    }

    #[Pure]
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    #[Pure]
    public function getBoundaryId(): string
    {
        return $this->boundaryId;
    }

    #[Pure]
    public function buildHeaders(): string
    {
        $body = '';
        $headers = $this->generateHeadersArray();
        $headers['Content-Type'] = 'multipart/alternative; boundary=' . $this->getBoundaryId();
        foreach ($headers as $name => $value) {
            $body .= sprintf('%s: %s%s', $name, $value, self::CRLF);
        }

        return $body;
    }

    #[Pure]
    public function buildBody(): string
    {
        $body = '';
        $boundaryId = $this->getBoundaryId();

        if (($text = $this->getTextBody()) !== null) {
            $body .= '--' . $boundaryId . self::CRLF;
            $body .= 'Content-Type: text/plain; charset=' . $this->charset . self::CRLF;
            $body .= 'Content-Transfer-Encoding: quoted-printable' . self::CRLF . self::CRLF;
            $body .= $this->encodeQuotedPrintable($text) . self::CRLF;
        }

        if (($html = $this->getHtmlBody()) !== null) {
            $body .= '--' . $boundaryId . self::CRLF;
            $body .= 'Content-Type: text/html; charset=' . $this->charset . self::CRLF;
            $body .= 'Content-Transfer-Encoding: quoted-printable' . self::CRLF . self::CRLF;
            $body .= $this->encodeQuotedPrintable($html) . self::CRLF;
        }
        $body .= '--' . $boundaryId . '--';

        return $body;
    }

    #[Pure]
    public function build(): string
    {
        return $this->buildHeaders() . self::CRLF . $this->buildBody() . self::CRLF;
    }

    /**
     * @param Message $message
     * @return array<string, string> The array of sanitized headers
     */
    #[Pure]
    public function generateHeadersArray(): array
    {
        $headers = [];

        foreach ($this->getHeaders() as $name => $value) {
            if (!isset($headers[$name])) {
                $headers[$name] = $this->sanitizeHeader($value);
            }
        }
        $headers['From'] = $this->getFrom()->mimeEncoded();
        $headers['To'] = implode(', ', $this->getTo());
        $headers['Date'] = $this->date;
        $headers['Subject'] = mb_encode_mimeheader($this->getSubject() ?? '', 'UTF-8', 'B');
        $headers['MIME-Version'] = '1.0';
        if ($this->getCc() !== []) {
            $headers['Cc'] = implode(', ', $this->getCc());
        }

        return $headers;
    }

    #[Pure]
    private function sanitizeHeader(string $value): string
    {
        return trim(str_replace(["\r", "\n"], '', $value));
    }

    #[Pure]
    public function getDate(): string
    {
        return $this->date;
    }

    public function getMessageId(): string
    {
        if ($this->from === null && !isset($this->headers['Message-ID'])) {
            throw new BadMethodCallException('Message ID can not be obtained before "From" is set');
        }

        if (!isset($this->headers['Message-ID'])) {
            $this->headers['Message-ID'] = sprintf(
                "<%s@%s>",
                substr(hash('md5', random_bytes(32)), 0, 12),
                $this->from->email()->host()
            );
        }

        return $this->headers['Message-ID'];
    }

    #[Pure]
    private function encodeQuotedPrintable(string $string): string
    {
        $string = quoted_printable_encode($string);
        // transform CR or LF to CRLF
        $string = preg_replace('~=0D(?!=0A)|(?<!=0D)=0A~', '=0D=0A', $string);
        // transform =0D=0A to CRLF
        $string = str_replace(["\t=0D=0A", ' =0D=0A', '=0D=0A'], ["=09\r\n", "=20\r\n", "\r\n"], $string);

        switch (ord(substr($string, -1))) {
            case 0x09:
                $string = substr_replace($string, '=09', -1);
                break;
            case 0x20:
                $string = substr_replace($string, '=20', -1);
                break;
        }

        return $string;
    }
}
