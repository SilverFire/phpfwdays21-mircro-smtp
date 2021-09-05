<?php
declare(strict_types=1);

namespace MicroMailer\Transport\SmtpTransport;

use JetBrains\PhpStorm\Pure;

class SmtpTransportConfig
{
    private ?string $host = null;

    private int $port = 25;

    private ?string $domain = null;

    private int $connectionTimeoutSeconds = 2;

    #[Pure]
    public function withHost(string $host): self
    {
        $self = clone $this;
        $self->host = $host;

        return $self;
    }

    #[Pure]
    public function withPort(int $port): self
    {
        $self = clone $this;
        $self->port = $port;

        return $self;
    }

    #[Pure]
    public function withDomain(string $domain): self
    {
        $self = clone $this;
        $self->domain = $domain;

        return $self;
    }

    #[Pure]
    public function withConnectionTimeoutSeconds(int $connectionTimeoutSeconds): self
    {
        $self = clone $this;
        $self->connectionTimeoutSeconds = $connectionTimeoutSeconds;

        return $self;
    }

    #[Pure]
    public function getHost(): ?string
    {
        return $this->host;
    }

    #[Pure]
    public function getPort(): int
    {
        return $this->port;
    }

    #[Pure]
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getConnectionTimeoutSeconds(): int
    {
        return $this->connectionTimeoutSeconds;
    }

    // TODO: authentication, encryption
}
