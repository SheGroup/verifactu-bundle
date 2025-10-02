<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Enum;

final class TaxType
{
    public const IVA = '01'; // Impuesto sobre el Valor Añadido (IVA)
    public const IPSI = '02'; // Impuesto sobre la Producción, los Servicios y la Importación (IPSI) de Ceuta y Melilla
    public const IGIC = '03'; // Impuesto General Indirecto Canario (IGIC)
    public const OTHER = '05'; // Otros

    public static function getValidValues(): array
    {
        return [
            self::IVA,
            self::IPSI,
            self::IGIC,
            self::OTHER,
        ];
    }
}
