<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Model\Response;

interface InvoiceClient
{
    public function sendInvoice(Invoice $invoice): Response;
}
