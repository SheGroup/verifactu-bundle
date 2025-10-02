<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use SheGroup\VerifactuBundle\Entity\Invoice;

interface InvoiceLinkGenerator
{
    public function generate(Invoice $invoice): string;
}
