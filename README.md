# What is it

This repository is a solution of a PHP fwdays 2021 [challenge by sendios (Genesis)](https://php_fwdays21_prize.tilda.ws/).

**It is tested to:**
- Send Plaintext and HTML messages;
- Support UTF-8 🔥, To, Cc, Bcc;
- Deliver messages to Gmail, Yahoo, Hotmail with a properly prepared server;
- Reuse active connections for batch email dispatching;
- Fallback to a reserve MX servers, when the first does not work;
- When a single email has many To, Cc, Bcc and they belong to different mail domains – the library makes
sure to deliver message to all the recipients.

# Installation

Make sure you have PHP 8.0.

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

Make sure that you are:
- sending an email from a warmed up IP address
- IP address you are using is not in Blacklists 
- The SPF records are configured accrdingly

# Sending a DKIM-signed test email

```bash
> #                        ⬇ DKIM private key ⬇ DKIM domain         ⬇ DKIM selector
> ./src/bin/sendSigned.php tests/data/dkim.key testdkim.silverfire.me silverfire \
                           from@email.com to@email.com "Test subject" "Test Body"
> #                        ⬆ from        ⬆ to         ⬆ subject      ⬆ text body

Delivery result: 
[to@email.com] – processed by "reception.mail-tester.com", result: 0 (OK)
```

Prepare a test DKIM private key, to be passed in a first argument.
Make sure the domain you are sending is configured properly for DKIM.

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
    new \MicroMailer\Transport\CraftSmtpTransport\ReceiverSmtpServersCollector()
);

$result = $transport->sendBatch(...$messages);
```
