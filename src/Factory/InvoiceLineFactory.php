<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Factory;

use SheGroup\VerifactuBundle\Entity\InvoiceLine;
use SheGroup\VerifactuBundle\Exception\InvalidInvoiceLineException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class InvoiceLineFactory
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /** @throws InvalidInvoiceLineException */
    public function create(
        float $baseAmount,
        float $taxRate,
        float $taxAmount,
        string $taxType = InvoiceLine::DEFAULT_TAX_TYPE,
        string $regimeType = InvoiceLine::DEFAULT_REGIME_TYPE,
        string $operationType = InvoiceLine::DEFAULT_OPERATION_TYPE
    ): InvoiceLine {
        $line = new InvoiceLine();
        $line->setBaseAmount($baseAmount);
        $line->setTaxRate($taxRate);
        $line->setTaxAmount($taxAmount);
        $line->setTaxType($taxType);
        $line->setRegimeType($regimeType);
        $line->setOperationType($operationType);
        $errors = $this->validator->validate($line);
        if (count($errors) > 0) {
            throw new InvalidInvoiceLineException($line, $errors);
        }

        return $line;
    }
}
