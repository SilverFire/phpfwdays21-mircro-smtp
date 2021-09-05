<?php

include __DIR__ . '/../../vendor/autoload.php';

(static function ($argv) {
    if (empty($argv[1])) {
        echo "Usage: send.php <FROM> <TO> <SUBJECT> <TEXT>";
        die;
    }

    $transport = new MicroMailer\Transport\CraftSmtpTransport(
        new \MicroMailer\Builder\MimeMessageBuilder(),
        new \MicroMailer\Transport\CraftSmtpTransport\ReceiverSmtpServersCollector(),
    );

    $message = (new \MicroMailer\ValueObject\Message())
        ->withFrom(\MicroMailer\ValueObject\Mailbox::fromString($argv[1]))
        ->withAddedTo(\MicroMailer\ValueObject\Mailbox::fromString($argv[2]))
        ->withSubject($argv[3])
        ->withTextBody($argv[4]);

    $result = $transport->send($message);

    var_dump($result);
})($argv);


