<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Enum;

final class RegimeType
{
    public const C01 = '01'; // Operación de régimen general
    public const C02 = '02'; // Exportación
    /**
     * Operaciones a las que se aplique el régimen especial de bienes usados, objetos de arte, antigüedades
     * y objetos de colección
     */
    public const C03 = '03';
    public const C04 = '04'; // Régimen especial del oro de inversión
    public const C05 = '05'; // Régimen especial de las agencias de viajes
    public const C06 = '06'; // Régimen especial grupo de entidades en IVA (nivel avanzado)
    public const C07 = '07'; // Régimen especial del criterio de caja
    public const C08 = '08'; // Operaciones sujetas al IPSI/IGIC
    /**
     * Facturación de las prestaciones de servicios de agencias de viaje que actúan como mediadoras en nombre
     * y por cuenta ajena (D.A.4ª RD1616/2012)
     */
    public const C09 = '09';
    /**
     * Cobros por cuenta de terceros de honorarios profesionales o de derechos derivados de la propiedad industrial,
     * de autor u otros por cuenta de sus socios, asociados o colegiados efectuados por sociedades, asociaciones,
     * colegios profesionales u otras entidades que realicen estas funciones de cobro
     */
    public const C10 = '10';
    public const C11 = '11'; // Operaciones de arrendamiento de local de negocio sujetas a retención
    /**
     * Factura con IVA pendiente de devengo en certificaciones de obra cuyo destinatario sea una Administración Pública
     */
    public const C14 = '14';
    public const C15 = '15'; // Factura con IVA pendiente de devengo en operaciones de tracto sucesivo
    /**
     * Operación acogida a alguno de los regímenes previstos en el Capítulo XI del Título IX (OSS o IOSS)
     * @noinspection SpellCheckingInspection
     */
    public const C17 = '17';
    public const C18 = '18'; // Recargo de equivalencia
    /**
     * Operaciones de actividades incluidas en el Régimen Especial de Agricultura, Ganadería y Pesca (REAGYP)
     * @noinspection SpellCheckingInspection
     */
    public const C19 = '19';
    public const C20 = '20'; // Régimen simplificado

    public static function getValidValues(): array
    {
        return [
            self::C01,
            self::C02,
            self::C03,
            self::C04,
            self::C05,
            self::C06,
            self::C07,
            self::C08,
            self::C09,
            self::C10,
            self::C11,
            self::C14,
            self::C15,
            self::C17,
            self::C18,
            self::C19,
            self::C20,
        ];
    }
}
