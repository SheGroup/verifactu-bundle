<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SheGroup\VerifactuBundle\Enum\OperationType;
use SheGroup\VerifactuBundle\Enum\RegimeType;
use SheGroup\VerifactuBundle\Enum\TaxType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="verifactu_invoice_line")
 * @ORM\Entity(repositoryClass="SheGroup\VerifactuBundle\Repository\DoctrineInvoiceLineRepository")
 */
class InvoiceLine
{
    public const DEFAULT_TAX_TYPE = TaxType::IVA;
    public const DEFAULT_REGIME_TYPE = RegimeType::C01;
    public const DEFAULT_OPERATION_TYPE = OperationType::S1;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=false)
     * @Assert\NotNull()
     */
    private float $baseAmount;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=false)
     * @Assert\NotNull()
     * @Assert\Range(min=0, max=100)
     */
    private float $taxRate;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=false)
     * @Assert\NotNull()
     */
    private float $taxAmount;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice(callback={TaxType::class, "getValidValues"})
     */
    private string $taxType;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice(callback={RegimeType::class, "getValidValues"})
     */
    private string $regimeType;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice(callback={OperationType::class, "getValidValues"})
     */
    private string $operationType;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="lines")
     * @ORM\JoinColumn(name="invoice_id", onDelete="CASCADE")
     */
    private ?Invoice $invoice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseAmount(): float
    {
        return $this->baseAmount;
    }

    public function setBaseAmount(float $baseAmount): void
    {
        $this->baseAmount = $baseAmount;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function setTaxRate(float $taxRate): void
    {
        $this->taxRate = $taxRate;
    }

    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(float $taxAmount): void
    {
        $this->taxAmount = $taxAmount;
    }

    public function getTaxType(): string
    {
        return $this->taxType;
    }

    public function setTaxType(string $taxType): void
    {
        $this->taxType = $taxType;
    }

    public function getRegimeType(): string
    {
        return $this->regimeType;
    }

    public function setRegimeType(string $regimeType): void
    {
        $this->regimeType = $regimeType;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function setOperationType(string $operationType): void
    {
        $this->operationType = $operationType;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    /**
     * @Assert\Callback()
     *
     * @noinspection PhpUnused
     */
    public function validateTaxAmount(ExecutionContextInterface $context): void
    {
        $calculatedTaxAmount = round($this->baseAmount * ($this->taxRate / 100), 2, PHP_ROUND_HALF_UP);
        $diff = abs($this->baseAmount * ($this->taxRate / 100) - $this->taxAmount);
        if (round($diff, 2, PHP_ROUND_HALF_UP) <= round(0.01, 2, PHP_ROUND_HALF_UP)) {
            return;
        }

        $context->buildViolation('Calculated tax amount was {{ calculated }}, {{ expected }} expected.')
            ->setParameter('{{ calculated }}', $calculatedTaxAmount)
            ->setParameter('{{ expected }}', $this->taxAmount)
            ->atPath('taxAmount')
            ->addViolation();
    }
}
