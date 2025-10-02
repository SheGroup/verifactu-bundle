<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Service;

use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Exception\CannotProcessInvoiceException;
use SheGroup\VerifactuBundle\Exception\CannotStoreInvoiceException;
use SheGroup\VerifactuBundle\Model\Response;
use SheGroup\VerifactuBundle\Repository\InvoiceRepository;

final class InvoiceProcessor
{
    private InvoiceRepository $invoiceRepository;
    private InvoiceClient $invoiceClient;
    private InvoiceStorer $invoiceStorer;

    public function __construct(
        InvoiceRepository $invoiceRepository,
        InvoiceClient $invoiceClient,
        InvoiceStorer $invoiceStorer
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceClient = $invoiceClient;
        $this->invoiceStorer = $invoiceStorer;
    }

    /** @throws CannotStoreInvoiceException|CannotProcessInvoiceException */
    public function storeAndProcess(Invoice $invoice): Response
    {
        $this->invoiceStorer->store($invoice);

        return $this->process($invoice);
    }

    /** @throws CannotProcessInvoiceException */
    public function process(Invoice $invoice): Response
    {
        $response = $this->invoiceClient->sendInvoice($invoice);
        if (!$response->isSuccess()) {
            throw new CannotProcessInvoiceException($invoice, $response);
        }

        $invoice->setCsv($response->getCsv());
        $invoice->setIsSent(true);
        $this->invoiceRepository->save($invoice);

        return $response;
    }
}
