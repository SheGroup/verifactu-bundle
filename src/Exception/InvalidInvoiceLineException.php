<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Exception;

use SheGroup\VerifactuBundle\Entity\InvoiceLine;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

final class InvalidInvoiceLineException extends InvalidEntityException
{
    private InvoiceLine $invoiceLine;

    public function __construct(
        InvoiceLine $invoiceLine,
        ConstraintViolationListInterface $errors,
        Throwable $previous = null
    ) {
        parent::__construct($errors, $previous);
        $this->invoiceLine = $invoiceLine;
    }

    /* @noinspection PhpUnused */
    public function getInvoiceLine(): InvoiceLine
    {
        return $this->invoiceLine;
    }
}
