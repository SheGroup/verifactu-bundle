<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Exception;

use SheGroup\VerifactuBundle\Entity\Invoice;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

final class InvalidInvoiceException extends InvalidEntityException
{
    private Invoice $invoice;

    public function __construct(
        Invoice $invoice,
        ?ConstraintViolationListInterface $errors = null,
        Throwable $previous = null
    ) {
        parent::__construct($errors, $previous);
        $this->invoice = $invoice;
    }

    /* @noinspection PhpUnused */
    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }
}
