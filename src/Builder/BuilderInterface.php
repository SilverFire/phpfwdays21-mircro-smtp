<?php
declare(strict_types=1);

namespace MicroMailer\Builder;

use MicroMailer\ValueObject\Message;

interface BuilderInterface
{
    public function build(Message $message): string;
}
