<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

final class AEATNumberFormatter implements NumberFormatter
{
    public function format(float $number): string
    {
        return number_format(round($number, 2, PHP_ROUND_HALF_UP), 2, '.', '');
    }
}
