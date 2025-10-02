<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Tests\Service;

use Exception;
use PHPUnit\Framework\TestCase;
use SheGroup\VerifactuBundle\Service\AEATNumberFormatter;

final class AEATNumberFormatterTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @dataProvider getCases
     */
    public function testCases(float $number, string $expected): void
    {
        $formatter = new AEATNumberFormatter();

        $formatted = $formatter->format($number);

        $this->assertEquals($expected, $formatted);
    }

    public function getCases(): array
    {
        return [
            'zero' => [0, '0.00'],
            'zero dot zero' => [0.0, '0.00'],
            'zero dot zero zero' => [0.00, '0.00'],
            'zero dot zero zero zero' => [0.000, '0.00'],
            'integer thousand' => [1234, '1234.00'],
            'thousand' => [1234.56, '1234.56'],
        ];
    }
}
