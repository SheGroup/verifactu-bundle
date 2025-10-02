<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Tests\Service;

use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Enum\InvoiceType;
use SheGroup\VerifactuBundle\Service\AEATInvoiceHasher;
use SheGroup\VerifactuBundle\Service\AEATNumberFormatter;

final class AEATInvoiceHasherTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @dataProvider getCases
     */
    public function testHashGeneration(
        string $number,
        string $date,
        string $issuerId,
        string $type,
        float $taxAmount,
        float $totalAmount,
        string $hashedAt,
        ?string $previousHash,
        string $expectedHash
    ): void {
        $invoice = $this->createInvoice(
            $number,
            new DateTimeImmutable($date),
            $issuerId,
            $type,
            $taxAmount,
            $totalAmount,
            new DateTimeImmutable($hashedAt),
            $previousHash
        );
        $hash = (new AEATInvoiceHasher(new AEATNumberFormatter()))->hash($invoice);

        $this->assertEquals($expectedHash, $hash);
    }

    public function getCases(): array
    {
        return [
            'first standard invoice' => [
                'F2023-001',
                '2023-09-19',
                'A12345678',
                InvoiceType::INVOICE,
                21.00,
                121.00,
                '2023-09-19T09:23:43',
                null,
                'BD85B3B8AE23EB463742BB5E0F75E704083C1533F3A22FD9DAC905931BA2C3B3',
            ],
            'standard invoice' => [
                'F2023-001',
                '2023-09-19',
                'A12345678',
                InvoiceType::INVOICE,
                21.00,
                121.00,
                '2023-09-19T09:23:43',
                '842C8F063919FC4F4E5CCDB0A5E3B7F65BDC5D4C865E812E8874F5B22A677274',
                '510639A815CD28BE16412460C7F5BFF5676FEA106BE495DF393FFCA1375BE189',
            ],
            'first corrective invoice' => [
                'R2023-001',
                '2023-09-20',
                'B87654321',
                InvoiceType::CORRECTIVE_80_1_2,
                -10.50,
                -60.50,
                '2023-09-20T10:12:04',
                null,
                '74EF57ADA05F440A2689762FFD336B8DDD36272B5FCFA90C1BA10D6270683C9C',
            ],
            'corrective invoice' => [
                'R2023-001',
                '2023-09-20',
                'B87654321',
                InvoiceType::CORRECTIVE_80_1_2,
                -10.50,
                -60.50,
                '2023-09-20T10:12:04',
                '510639A815CD28BE16412460C7F5BFF5676FEA106BE495DF393FFCA1375BE189',
                'D66F0148DC97E6A142DB0D713B5CDDA6AB26D73B9802FADE22E34C8938D43D03',
            ],
            'first zero amount invoice' => [
                'F2023-002',
                '2023-09-21',
                'C11223344',
                InvoiceType::INVOICE,
                0.00,
                0.00,
                '2023-09-21T17:16:28',
                null,
                '2CE744B042C38913B915FF63530AED7E09CDD401DADB7E455819E52EB699840B',
            ],
            'zero amount invoice' => [
                'F2023-002',
                '2023-09-21',
                'C11223344',
                InvoiceType::INVOICE,
                0.00,
                0.00,
                '2023-09-21T17:16:28',
                '2DCC734AF229567067119B54668F8A659A8B8ED72CA0817018E79B6AFACE70B2',
                '31C1BE89755CA2A229F1A77147E50B5E7DB446689B3D8BF027BCF5E7F66841BB',
            ],
        ];
    }

    private function createInvoice(
        string $number,
        DateTimeImmutable $date,
        string $issuerId,
        string $type,
        float $taxAmount,
        float $totalAmount,
        DateTimeImmutable $hashedAt,
        ?string $previousHash = null
    ): Invoice {
        $invoice = new Invoice($number, $date, $issuerId, 'Acme Inc.', $type);
        $invoice->setDescription('Test');
        $invoice->setTotalTaxAmount($taxAmount);
        $invoice->setTotalAmount($totalAmount);
        $invoice->setHashedAt($hashedAt);
        if ($previousHash) {
            $previousInvoice = new Invoice('', new DateTimeImmutable(), $issuerId, 'Acme Inc.');
            $previousInvoice->setHash($previousHash);
            $invoice->setPreviousInvoice($previousInvoice);
        }

        return $invoice;
    }
}
