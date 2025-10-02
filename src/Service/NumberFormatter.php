<?php


declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

interface NumberFormatter
{
    public function format(float $number): string;
}
