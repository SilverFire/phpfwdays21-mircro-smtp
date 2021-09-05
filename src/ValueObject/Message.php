<?php
declare(strict_types=1);

namespace MicroMailer\ValueObject;

use JetBrains\PhpStorm\Pure;

class Message
{
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
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @return list<Mailbox>
     */
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
    public function withAddedHeader(string $name, string $value): self
    {
        $self = clone $this;
        $self->headers[$name] = $value;

        return $self;
    }

    public function withSubject(string $subject): self
    {
        $self = clone $this;
        $self->subject = $subject;

        return $self;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    // TODO: Implement attachments
    // TODO: Add replyTo
}
