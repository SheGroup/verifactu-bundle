<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Enum;

final class CorrectiveType
{
    public const SUBSTITUTION = 'S'; // SUSTITUTIVA
    public const DIFFERENCE = 'I'; // INCREMENTAL

    public static function getValidValues(): array
    {
        return [
            self::SUBSTITUTION,
            self::DIFFERENCE,
        ];
    }
}
