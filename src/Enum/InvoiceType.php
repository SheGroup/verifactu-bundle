<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Enum;

final class InvoiceType
{
    public const INVOICE = 'F1'; // FACTURA (ART. 6, 7.2 Y 7.3 DEL RD 1619/2012)
    public const SIMPLIFIED = 'F2'; // SIMPLIFICADA O SIN IDENTIFICACIÓN DEL DESTINATARIO ART. 6.1.D) RD 1619/2012
    public const SUBSTITUTIVE = 'F3'; // EMITIDA EN SUSTITUCIÓN DE FACTURAS SIMPLIFICADAS FACTURADAS Y DECLARADAS
    public const CORRECTIVE_80_1_2 = 'R1'; // FACTURA RECTIFICATIVA (Art 80.1 y 80.2 y error fundado en derecho)
    public const CORRECTIVE_80_3 = 'R2'; // FACTURA RECTIFICATIVA (Art. 80.3)
    public const CORRECTIVE_80_4 = 'R3'; // FACTURA RECTIFICATIVA (Art. 80.4)
    public const CORRECTIVE_OTHERS = 'R4'; // FACTURA RECTIFICATIVA (Resto)
    public const CORRECTIVE_SIMPLIFIED = 'R5'; // FACTURA RECTIFICATIVA EN FACTURAS SIMPLIFICADAS

    public static function getValidValues(): array
    {
        return [
            self::INVOICE,
            self::SIMPLIFIED,
            self::SUBSTITUTIVE,
            self::CORRECTIVE_80_1_2,
            self::CORRECTIVE_80_3,
            self::CORRECTIVE_80_4,
            self::CORRECTIVE_OTHERS,
            self::CORRECTIVE_SIMPLIFIED,
        ];
    }

    public static function isCorrective(string $type): bool
    {
        return in_array($type, self::getCorrectiveTypes(), true);
    }

    public static function isSimplified(string $type): bool
    {
        return in_array($type, self::getSimplifiedTypes(), true);
    }

    private static function getCorrectiveTypes(): array
    {
        return [
            self::CORRECTIVE_80_1_2,
            self::CORRECTIVE_80_3,
            self::CORRECTIVE_80_4,
            self::CORRECTIVE_OTHERS,
            self::CORRECTIVE_SIMPLIFIED,
        ];
    }

    private static function getSimplifiedTypes(): array
    {
        return [self::SIMPLIFIED, self::CORRECTIVE_SIMPLIFIED];
    }
}
