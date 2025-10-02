<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\tests\Service;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Model\ComputerSystem;
use SheGroup\VerifactuBundle\Service\AEATInvoiceXmlGenerator;
use SheGroup\VerifactuBundle\Service\AEATNumberFormatter;

final class AEATInvoiceXmlGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $computerSystem = new ComputerSystem(
            false,
            'AS',
            'AcmeSoft',
            '12345678Z',
            'Acme Inc.',
            '1.0.0',
            '1231412',
            true,
            false,
            false
        );
        $hashGenerator = new AEATInvoiceXmlGenerator($computerSystem, new AEATNumberFormatter());
        $invoice = new Invoice('6346253523', new DateTimeImmutable('2021-03-12'), 'R1234', 'Acme Inc.');
        $invoice->setDescription('Test');
        $invoice->setHashedAt(new DateTimeImmutable('2021-03-12 21:03:15'));
        $invoice->setHash('C97134DB989C91973F1ACBF17A626F139615869C0DE972B5374E8AB90F7E02A5');
        $expected = $this->cleanSpaces(file_get_contents(sprintf('%s/xml/invoice-xml-generator.xml', __DIR__)));

        $result = $this->cleanSpaces($hashGenerator->generate($invoice));

        $this->assertEquals($expected, $result);
    }

    private function cleanSpaces(string $xml): string
    {
        $xml = str_replace(["\r\n", "\n", "\t"], ' ', $xml);
        $xml = preg_replace('/ +/', ' ', $xml);

        return trim(str_replace(' <', '<', $xml));
    }
}
