<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Exception\CannotStoreInvoiceException;
use SheGroup\VerifactuBundle\Repository\InvoiceRepository;
use Throwable;

final class InvoiceStorer
{
    private InvoiceRepository $repository;
    private HashLock $hashLock;
    private InvoiceHasher $invoiceHasher;

    public function __construct(InvoiceRepository $repository, HashLock $hashLock, InvoiceHasher $invoiceHasher)
    {
        $this->repository = $repository;
        $this->hashLock = $hashLock;
        $this->invoiceHasher = $invoiceHasher;
    }

    /** @throws CannotStoreInvoiceException */
    public function store(Invoice $invoice): void
    {
        try {
            $this->hashLock->execute(function () use ($invoice): void {
                $invoice->setPreviousInvoice($this->repository->getLast());
                $invoice->setHash($this->invoiceHasher->hash($invoice));
                $this->repository->save($invoice);
            });
        } catch (Throwable $e) {
            throw new CannotStoreInvoiceException($invoice, $e);
        }
    }
}
