<?php

include __DIR__ . '/../../vendor/autoload.php';

(static function ($argv) {
    if (empty($argv[1])) {
        echo "Usage: send.php <FROM> <TO> <SUBJECT> <TEXT>";
        die;
    }

    $transport = new MicroMailer\Transport\CraftSmtpTransport(
        new \MicroMailer\Transport\CraftSmtpTransport\ReceiverSmtpServersCollector(),
    );

    $message = (new \MicroMailer\ValueObject\Message())
        ->withFrom(\MicroMailer\ValueObject\Mailbox::fromString($argv[1]))
        ->withAddedTo(\MicroMailer\ValueObject\Mailbox::fromString($argv[2]))
        ->withSubject($argv[3])
        ->withTextBody($argv[4]);

    $result = $transport->send($message);

    echo "Delivery result: \n";
    foreach ($result->perEmail as $email => $result) {
        printf("[%s] – processed by \"%s\", result: %s (%s)\n", $email, $result[0], $result[1], $result[2] ?? 'OK');
    }
})($argv);


