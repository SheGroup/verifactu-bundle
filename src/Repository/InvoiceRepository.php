<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Repository;

use SheGroup\VerifactuBundle\Entity\Invoice;

interface InvoiceRepository
{
    public function findById(int $id): ?Invoice;

    public function findByHash(string $hash): ?Invoice;

    public function findByNumber(string $number): ?Invoice;

    public function getLast(): ?Invoice;

    public function getPrevious(Invoice $invoice): ?Invoice;

    /** @return Invoice[] */
    public function getPendingToSend(): array;

    public function save(Invoice $invoice): void;

    public function remove(Invoice $invoice): void;
}
