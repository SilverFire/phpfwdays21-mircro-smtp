<?php
declare(strict_types=1);

namespace MicroMailer\Signer\Dkim;

/**
 * Class DkimOptions
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
final class DkimOptions
{
    public const CANON_SIMPLE = 'simple';
    public const CANON_RELAXED = 'relaxed';

    public const ALGO_SHA256 = 'rsa-sha256';

    /**
     * @var string
     * @psalm-var self::ALGO_*
     */
    public string $algorithm = self::ALGO_SHA256;
    public int $signatureExpirationDelay = 0;
    public bool $bodyShowLength = false;
    /**
     * @var string
     * @psalm-var self::CANON_*
     */
    public string $headerCanon = self::CANON_RELAXED;
    /**
     * @var string
     * @psalm-var self::CANON_*
     */
    public string $bodyCanon = self::CANON_RELAXED;
    /** @var string[] */
    public array $headersToIgnore = [];

    /**
     * @return string[]
     */
    public function getHeadersToIgnore(): array
    {
        $headersToIgnore = array_map('strtolower', $this->headersToIgnore);
        $headersToIgnore[] = 'return-path';
        $headersToIgnore[] = 'x-transport';

        return array_filter($headersToIgnore, static fn(string $name) => $name !== 'from');
    }
}
