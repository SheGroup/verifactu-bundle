<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Exception;

use RuntimeException;
use SheGroup\VerifactuBundle\Entity\Invoice;
use Throwable;

final class CannotStoreInvoiceException extends RuntimeException
{
    private Invoice $invoice;

    public function __construct(Invoice $invoice, Throwable $previous = null)
    {
        parent::__construct('', 0, $previous);
        $this->invoice = $invoice;
    }

    /* @noinspection PhpUnused */
    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }
}
