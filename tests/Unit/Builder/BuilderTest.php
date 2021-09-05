<?php
declare(strict_types=1);

namespace MicroMailer\tests\Unit\Builder;

use JetBrains\PhpStorm\Pure;
use MicroMailer\ValueObject\Mailbox;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    protected ?Mailbox $from;

    #[Pure]
    public function withFrom(Mailbox $from): self
    {
        $self = clone $this;
        $self->from = $from;

        return $self;
    }
}
