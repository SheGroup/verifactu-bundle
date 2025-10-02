<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Factory;

use DateTimeInterface;
use SheGroup\VerifactuBundle\Entity\Invoice;
use SheGroup\VerifactuBundle\Entity\InvoiceLine;
use SheGroup\VerifactuBundle\Entity\InvoiceRecipient;
use SheGroup\VerifactuBundle\Enum\CorrectiveType;
use SheGroup\VerifactuBundle\Enum\InvoiceType;
use SheGroup\VerifactuBundle\Exception\InvalidInvoiceException;
use SheGroup\VerifactuBundle\Repository\InvoiceRepository;
use SheGroup\VerifactuBundle\Service\HashLock;
use SheGroup\VerifactuBundle\Service\InvoiceHasher;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

final class InvoiceFactory
{
    private InvoiceRepository $repository;
    private HashLock $hashLock;
    private InvoiceHasher $invoiceHasher;
    private ValidatorInterface $validator;

    public function __construct(
        InvoiceRepository $repository,
        HashLock $hashLock,
        InvoiceHasher $invoiceHasher,
        ValidatorInterface $validator
    ) {
        $this->repository = $repository;
        $this->hashLock = $hashLock;
        $this->invoiceHasher = $invoiceHasher;
        $this->validator = $validator;
    }

    /**
     * @param InvoiceRecipient[] $recipients
     * @param InvoiceLine[] $lines
     *
     * @throws InvalidInvoiceException
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function create(
        string $number,
        DateTimeInterface $date,
        string $issuerId,
        string $issuerName,
        string $invoiceType,
        string $description,
        float $totalTaxAmount,
        float $totalAmount,
        array $recipients,
        array $lines
    ): Invoice {
        $invoice = new Invoice($number, $date, $issuerId, $issuerName, $invoiceType);
        $this->fillInvoice($invoice, $description, $totalTaxAmount, $totalAmount, $recipients, $lines);
        $this->calculateHash($invoice);
        $this->validate($invoice);

        return $invoice;
    }

    /**
     * @param InvoiceLine[] $lines
     *
     * @throws InvalidInvoiceException
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function createCorrectiveByDifference(
        string $sourceNumber,
        string $number,
        DateTimeInterface $date,
        string $issuerId,
        string $issuerName,
        string $description,
        float $totalTaxAmount,
        float $totalAmount,
        array $recipients,
        array $lines,
        string $invoiceType = InvoiceType::CORRECTIVE_OTHERS
    ): Invoice {
        $invoice = new Invoice($number, $date, $issuerId, $issuerName, $invoiceType);
        $source = $this->repository->findByNumber($sourceNumber);
        if (!$source) {
            throw new InvalidInvoiceException($invoice, null);
        }

        $this->fillInvoice($invoice, $description, $totalTaxAmount, $totalAmount, $recipients, $lines);
        $invoice->setCorrectiveType(CorrectiveType::DIFFERENCE);
        $invoice->addCorrectedInvoice($source);
        $this->calculateHash($invoice);
        $this->validate($invoice);

        return $invoice;
    }

    /**
     * @param InvoiceRecipient[] $recipients
     * @param InvoiceLine[] $lines
     *
     * @throws InvalidInvoiceException
     */
    public function fillInvoice(
        Invoice $invoice,
        string $description,
        float $totalTaxAmount,
        float $totalAmount,
        array $recipients,
        array $lines
    ): Invoice {
        $invoice->setDescription($description);
        $invoice->setTotalTaxAmount($totalTaxAmount);
        $invoice->setTotalAmount($totalAmount);
        $invoice->setIsSent(false);
        foreach ($recipients as $recipient) {
            $invoice->addRecipient($recipient);
        }
        foreach ($lines as $line) {
            $invoice->addLine($line);
        }

        return $invoice;
    }

    /** @throws InvalidInvoiceException */
    private function validate(Invoice $invoice): void
    {
        $errors = $this->validator->validate($invoice);
        if (count($errors) > 0) {
            throw new InvalidInvoiceException($invoice, $errors);
        }
    }

    /** @throws InvalidInvoiceException */
    private function calculateHash(Invoice $invoice): void
    {
        try {
            $this->hashLock->execute(function () use ($invoice): void {
                $invoice->setPreviousInvoice($this->repository->getLast());
                $invoice->setHash($this->invoiceHasher->hash($invoice));
            });
        } catch (Throwable $e) {
            throw new InvalidInvoiceException($invoice, null, $e);
        }
    }
}
