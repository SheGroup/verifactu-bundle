<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use SheGroup\VerifactuBundle\Entity\Invoice;

final class AEATInvoiceHasher implements InvoiceHasher
{
    private NumberFormatter $numberFormatter;

    public function __construct(NumberFormatter $numberFormatter)
    {
        $this->numberFormatter = $numberFormatter;
    }

    public function hash(Invoice $invoice): string
    {
        /* @noinspection SpellCheckingInspection */
        $params = [
            'IDEmisorFactura' => $invoice->getIssuerId(),
            'NumSerieFactura' => $invoice->getNumber(),
            'FechaExpedicionFactura' => $invoice->getDate()->format('d-m-Y'),
            'TipoFactura' => $invoice->getType(),
            'CuotaTotal' => $this->numberFormatter->format($invoice->getTotalTaxAmount()),
            'ImporteTotal' => $this->numberFormatter->format($invoice->getTotalAmount()),
            'Huella' => $invoice->getPreviousHash() ?? '',
            'FechaHoraHusoGenRegistro' => $invoice->getHashedAt()->format('c'),
        ];

        return strtoupper(hash('sha256', urldecode(http_build_query($params))));
    }
}
