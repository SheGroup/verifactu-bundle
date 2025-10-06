<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Enum;

final class OperationType
{
    public const S1 = 'S1'; // OPERACIÓN SUJETA Y NO EXENTA - SIN INVERSIÓN DEL SUJETO PASIVO
    public const S2 = 'S2'; // OPERACIÓN SUJETA Y NO EXENTA - CON INVERSIÓN DEL SUJETO PASIVO
    public const N1 = 'N1'; // OPERACIÓN NO SUJETA ARTÍCULO 7, 14, OTROS
    public const N2 = 'N2'; // OPERACIÓN NO SUJETA POR REGLAS DE LOCALIZACIÓN

    public static function getValidValues(): array
    {
        return [
            self::S1,
            self::S2,
            self::N1,
            self::N2,
        ];
    }

    public static function isExempt(string $type): bool
    {
        return in_array($type, [self::N1, self::N2], true);
    }
}
