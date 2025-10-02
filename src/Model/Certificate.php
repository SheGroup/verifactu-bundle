<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Model;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use RuntimeException;
use Throwable;

final class Certificate
{
    private bool $enabled;
    private string $path;
    private string $expirationWarning;
    private ?string $password;

    public function __construct(bool $enabled, string $expirationWarning, string $path, ?string $password)
    {
        $this->enabled = $enabled;
        $this->path = $path;
        $this->expirationWarning = $expirationWarning;
        $this->password = $password;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPassword(): ?string
    {
        return $this->password ?: null;
    }

    /* @noinspection PhpUnused */
    public function hasPassword(): bool
    {
        return (bool) $this->password;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /** @throws RuntimeException */
    public function getCertificateExpiration(): DateTimeInterface
    {
        $metadata = self::getMetadata($this->path, $this->password);
        try {
            $date = new DateTimeImmutable(sprintf('@%s', $metadata['validTo_time_t'] ?? 0));
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Invalid certificate expiration: "%s"', $this->path));
        }

        return $date;
    }

    /** @throws Throwable */
    public function expiresSoon(): bool
    {
        return new DateTimeImmutable(sprintf('now +%s', $this->expirationWarning)) >= $this->getCertificateExpiration();
    }

    /** @throws Throwable */
    public function isExpired(): bool
    {
        return new DateTimeImmutable() >= $this->getCertificateExpiration();
    }

    public static function isValidCertificate(string $path, string $password): bool
    {
        try {
            self::getMetadata($path, $password);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /** @throws RuntimeException */
    private static function getMetadata(string $path, string $password): array
    {
        $cert = file_get_contents($path);
        if (!$cert) {
            throw new RuntimeException(sprintf('Certificate file "%s" not found', $path));
        }

        $certs = [];
        $info = openssl_pkcs12_read($cert, $certs, $password ?: '');
        if (!$info) {
            throw new RuntimeException(sprintf('Invalid certificate or password: "%s"', $path));
        }

        $metadata = openssl_x509_parse($certs['cert'] ?? '');
        if (!$metadata) {
            throw new RuntimeException(sprintf('Invalid x509 certificate: "%s"', $path));
        }

        return $metadata;
    }
}
