<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Enum;

use SheGroup\VerifactuBundle\Entity\InvoiceRecipient;

final class RecipientIdType
{
    public const VAT = '02'; // NIF-IVA
    public const PASSPORT = '03'; // Pasaporte
    public const NATIONAL_ID = '04'; // IDEnPaisResidencia
    public const RESIDENCE = '05'; // Certificado Residencia
    public const OTHER = '06'; // Otro documento Probatorio
    public const UNREGISTERED = '07'; // No Censado

    public static function getValidValues(): array
    {
        return [
            self::VAT,
            self::PASSPORT,
            self::NATIONAL_ID,
            self::RESIDENCE,
            self::OTHER,
            self::UNREGISTERED,
        ];
    }

    public static function detectType(string $type, string $country): string
    {
        if (InvoiceRecipient::LOCAL_COUNTRY === $country) {
            return self::detectLocalType($type);
        }

        return self::detectForeignType($type);
    }

    private static function detectLocalType(string $id): string
    {
        if (preg_match('/^(\d{8})([A-Z])$/ui', $id)) {
            return self::NATIONAL_ID;
        }
        if (preg_match('/^[XYZ]\d{7,8}[A-Z]$/ui', $id)) {
            return self::RESIDENCE;
        }
        if (preg_match('/^([ABCDEFGHJKLMNPQRSUVW])(\d{7})([0-9A-J])$/ui', $id)) {
            return self::VAT;
        }

        return self::OTHER;
    }

    private static function detectForeignType(string $id): string
    {
        if (!$id) {
            return self::UNREGISTERED;
        }

        if (preg_match('/^(?:\d{9}|[A-Z]\d{7}|\d{8}[A-Z]|[CFGHJK]\d{8})$/ui', $id)) {
            return self::PASSPORT;
        }
        if (preg_match('/^(?:[A-Z]{2}\d{7}|[A-Z]\d{8}|[A-Z]{2}\d{6})$/ui', $id)) {
            return self::PASSPORT;
        }

        return self::OTHER;
    }
}
