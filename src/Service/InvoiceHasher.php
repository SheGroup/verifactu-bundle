<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use SheGroup\VerifactuBundle\Entity\Invoice;

interface InvoiceHasher
{
    public function hash(Invoice $invoice): string;
}
