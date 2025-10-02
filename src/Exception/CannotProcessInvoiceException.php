<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Exception;

use RuntimeException;
use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Model\Response;
use Throwable;

final class CannotProcessInvoiceException extends RuntimeException
{
    private Invoice $invoice;
    private Response $response;

    public function __construct(Invoice $invoice, Response $response, Throwable $previous = null)
    {
        parent::__construct('', 0, $previous);
        $this->invoice = $invoice;
        $this->response = $response;
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
