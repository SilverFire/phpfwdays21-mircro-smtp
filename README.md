# Installation

```bash
git clone git@github.com:SilverFire/phpfwdays21-mircro-smtp.git
cd phpfwdays21-mircro-smtp
composer install
```

# Running tests

The repository has a lot of unit tests:

```bash
./vendor/bin/phpunit
```

# Sending a single test email

```bash
> ./src/bin/send.php from@email.com to@email.com "Test subject" "Test Body"

Delivery result: 
[test-7oh2gdzlt@srv1.mail-tester.com] – processed by "reception.mail-tester.com", result: 0 (OK)
```

# Send a batch of emails

Use this repository as a library and try the following code:

```php
// Prepare messages
$messages = [
    (new \MicroMailer\ValueObject\Message())
        ->withFrom(\MicroMailer\ValueObject\Mailbox::fromString('email@test.com'))
        ->withAddedTo(\MicroMailer\ValueObject\Mailbox::fromString('email2@test.com'))
        ->withSubject('First letter')
        ->withTextBody('Body') // <<< Plaintext body
        
    (new \MicroMailer\ValueObject\Message())
        ->withFrom(\MicroMailer\ValueObject\Mailbox::fromString('email2@test.com'))
        ->withAddedTo(\MicroMailer\ValueObject\Mailbox::fromString('email3@test.com'))
        ->withSubject('Second letter')
        ->withHtmlBody('<b>TEST</b>') // <<< HTML body
        ->withAddedHeader('X-Version', '2.3.1') // <<< Additional headers
];

// Create a transport
$transport = new MicroMailer\Transport\CraftSmtpTransport(
    new \MicroMailer\Builder\MimeMessageBuilder(),
    new \MicroMailer\Transport\CraftSmtpTransport\ReceiverSmtpServersCollector(),
);

$result = $transport->sendBatch(...$messages);
```
