<?php
declare(strict_types=1);

namespace MicroMailer\Signer\Dkim;

use InvalidArgumentException;
use MicroMailer\Signer\SignerInterface;
use MicroMailer\ValueObject\Message;
use OpenSSLAsymmetricKey;
use OpenSSLCertificate;

use const OPENSSL_ALGO_SHA256;

/**
 * Class DkimSigner signs a {@see Message} with a DKIM signature.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class DkimSigner implements SignerInterface
{
    private DkimOptions $options;
    private OpenSSLAsymmetricKey $privateKey;

    /**
     * DkimSigner constructor.
     *
     * @param OpenSSLAsymmetricKey|OpenSSLCertificate|array|string $privateKey a OpenSSL key object or path to a key file
     * @param string $domain the domain name this key is intended for, e.g. `example.com`
     * @param string $selector the selector for the domain name, where a public key can be found, e.g.
     * for `selector` the `selector._domainkey.example.com` will be queried
     * @param string|null $passphrase a passphrase to unlock a key
     * @throws DkimException
     */
    public function __construct(
        OpenSSLAsymmetricKey|OpenSSLCertificate|array|string $privateKey,
        private string $domain,
        private string $selector,
        ?DkimOptions $options = null,
        ?string $passphrase = null,
    ) {
        $this->options = $options ??= new DkimOptions();
        $key = openssl_pkey_get_private($privateKey, $passphrase);
        if ($key === false) {
            throw new DkimException('Failed to load private key: ' . openssl_error_string());
        }

        if ($options->bodyCanon !== DkimOptions::CANON_RELAXED || $options->headerCanon !== DkimOptions::CANON_RELAXED) {
            throw new InvalidArgumentException('DkimSigner supports only Relaxed body and header signing');
        }
        if ($options->algorithm !== DkimOptions::ALGO_SHA256) {
            throw new InvalidArgumentException('DkimSigner supports only SHA256 signing');
        }

        $this->privateKey = $key;
    }

    public function sign(Message $message): Message
    {
        $headersToIgnore = $this->options->getHeadersToIgnore();

        $signedHeaderNames = [];
        $headersBody = '';
        foreach ($message->generateHeadersArray() as $name => $value) {
            if (in_array($name, $headersToIgnore, true)) {
                continue;
            }

            $signedHeaderNames[] = $name;
            $headersBody .= $this->canonHeader($name, $value);
        }

        [$bodyHash, $bodyLength] = $this->hashBody($message->buildBody());

        $dkimConfiguration = $this->buildDkimHeaderConfiguration($bodyHash, $signedHeaderNames, $bodyLength);
        $headersBody .= trim($this->canonHeader('DKIM-Signature', $dkimConfiguration));

        if (!openssl_sign($headersBody, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
            throw new DkimException('Unable to sign DKIM: ' . openssl_error_string());
        }

        return $message
            ->withHeader(
                'DKIM-Signature',
                $dkimConfiguration . trim(base64_encode($signature))
            );
    }

    private function buildDkimHeaderConfiguration(string $bodyHash, array $signedHeaderNames, int $bodyLength): string
    {
        $params = [
            'v' => '1',
            'q' => 'dns/txt',
            'a' => $this->options->algorithm,
            'bh' => base64_encode($bodyHash),
            'd' => $this->domain,
            'h' => implode(': ', $signedHeaderNames),
            'i' => '@' . $this->domain,
            's' => $this->selector,
            't' => $this->getUnixTime(),
            'c' => sprintf("%s/%s", $this->options->headerCanon, $this->options->bodyCanon),
        ];

        if ($this->options->bodyShowLength) {
            $params['l'] = $bodyLength;
        }
        if ($this->options->signatureExpirationDelay > 0) {
            $params['x'] = $params['t'] + $this->options->signatureExpirationDelay;
        }
        $value = '';
        foreach ($params as $k => $v) {
            $value .= $k . '=' . $v . '; ';
        }
        $value .= 'b=';

        return trim($value);
    }

    private function canonHeader(string $name, string $value): string
    {
        // Unfold all header field continuation lines
        $value = preg_replace("/\r\n(?=[\x20\x09])/", '', $value);
        // Convert all sequences of one or more WSP characters to a single SP
        $value = preg_replace("/[\x20\x09]+/", ' ', $value);
        // Devare all WSP characters at the end of each unfolded header field
        $value = preg_replace("/[\x20\x09]+$/", '', $value);
        // Remove any WSP characters remaining before and after the colon

        return strtolower($name) . ':' . $value . Message::CRLF;
    }

    /**
     * @param string $body
     * @return array
     */
    private function hashBody(string $body): array
    {
        // Ignore all whitespace at the end of lines.
        $result = preg_replace("/[\x20\x09]+(?=\r\n)/", '', $body);
        // Reduce all sequences of WSP within a line to a single SP
        $result = preg_replace("/[\x20\x09]+/", ' ', $result);
        // Ignore all empty lines at the end of the message body.
        $result = preg_replace("/(\r\n)+$/", '', $result) . Message::CRLF;

        return [hash('sha256', $result, true), strlen($result)];
    }

    protected function getUnixTime(): int
    {
        return time();
    }
}
