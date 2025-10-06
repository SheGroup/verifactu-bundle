<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Exception;

use SheGroup\VerifactuBundle\Entity\InvoiceRecipient;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

final class InvalidInvoiceRecipientException extends InvalidEntityException
{
    private InvoiceRecipient $invoiceRecipient;

    public function __construct(
        InvoiceRecipient $recipient,
        ConstraintViolationListInterface $errors,
        Throwable $previous = null
    ) {
        parent::__construct($errors, $previous);
        $this->message = sprintf(
            'Invalid invoice recipient: %s (%s)',
            $recipient->getName(),
            $recipient->getRecipientId()
        );
        $this->invoiceRecipient = $recipient;
    }

    /* @noinspection PhpUnused */
    public function getInvoiceRecipient(): InvoiceRecipient
    {
        return $this->invoiceRecipient;
    }
}
