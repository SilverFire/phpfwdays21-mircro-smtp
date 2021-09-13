<?php

include __DIR__ . '/../../vendor/autoload.php';

(static function ($argv) {
    if (empty($argv[1])) {
        echo "Usage: send.php <PATH_TO_PRIVATE_KEY> <DOMAIN> <SELECTOR> <FROM> <TO> <SUBJECT> <TEXT>";
        echo 'Example: tests/data/dkim.key testdkim.silverfire.me silverfire my@testdkim.silverfire.me hello@example.com "Test subj" "Test Body"';
        die;
    }

    $transport = new MicroMailer\Transport\CraftSmtpTransport(
        new \MicroMailer\Transport\CraftSmtpTransport\ReceiverSmtpServersCollector(),
    );

    $signer = new \MicroMailer\Signer\Dkim\DkimSigner(file_get_contents($argv[1]), $argv[2], $argv[3]);

    $message = (new \MicroMailer\ValueObject\Message())
        ->withFrom(\MicroMailer\ValueObject\Mailbox::fromString($argv[4]))
        ->withAddedTo(\MicroMailer\ValueObject\Mailbox::fromString($argv[5]))
        ->withSubject($argv[6])
        ->withTextBody($argv[7]);

    $signedMessage = $signer->sign($message);

    $result = $transport->send($signedMessage);

    echo "Delivery result: \n";
    foreach ($result->perEmail as $email => $result) {
        printf("[%s] – processed by \"%s\", result: %s (%s)\n", $email, $result[0], $result[1], $result[2] ?? 'OK');
    }
})($argv);


