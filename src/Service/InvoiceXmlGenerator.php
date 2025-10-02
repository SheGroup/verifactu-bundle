<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use SheGroup\VerifactuBundle\Entity\Invoice;

interface InvoiceXmlGenerator
{
    public function generate(Invoice $invoice): string;

    public function normalize(Invoice $invoice): array;
}
