<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Model\ComputerSystem;

final class AEATInvoiceLinkGenerator implements InvoiceLinkGenerator
{
    private ComputerSystem $computerSystem;
    private NumberFormatter $numberFormatter;

    public function __construct(
        ComputerSystem $computerSystem,
        NumberFormatter $numberFormatter
    ) {
        $this->computerSystem = $computerSystem;
        $this->numberFormatter = $numberFormatter;
    }

    public function generate(Invoice $invoice): string
    {
        /* @noinspection SpellCheckingInspection */
        return sprintf(
            '%s/wlpl/TIKE-CONT/ValidarQR?nif=%s&numserie=%s&fecha=%s&importe=%s',
            $this->computerSystem->getApiDomain(),
            $invoice->getIssuerId(),
            $invoice->getNumber(),
            $invoice->getDate()->format('d-m-Y'),
            $this->numberFormatter->format($invoice->getTotalAmount())
        );
    }
}
