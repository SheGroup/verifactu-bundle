<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SheGroup\VerifactuBundle\Enum\CorrectiveType;
use SheGroup\VerifactuBundle\Enum\InvoiceType;
use SheGroup\VerifactuBundle\Exception\InvalidInvoiceException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Throwable;

/**
 * @ORM\Table(
 *     name="verifactu_invoice",
 *     indexes={
 *         @ORM\Index(columns={"is_sent"}),
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"number"}),
 *         @ORM\UniqueConstraint(columns={"hash"}),
 *     },
 * )
 * @ORM\Entity(repositoryClass="SheGroup\VerifactuBundle\Repository\DoctrineInvoiceRepository")
 */
class Invoice
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(max=60)
     */
    private string $number;

    /** @ORM\Column(type="string", length=64, nullable=true) */
    private ?string $csv = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Assert\Length(max=60)
     */
    private ?string $previousNumber = null;

    /**
     * @ORM\Column(type="date_immutable", nullable=false)
     * @Assert\NotBlank()
     */
    private DateTimeInterface $date;

    /** @ORM\Column(type="date_immutable", nullable=true) */
    private ?DateTimeInterface $previousDate;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=9, max=9)
     */
    private string $issuerId;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\Length(min=9, max=9)
     */
    private ?string $previousIssuerId = null;

    /**
     * @ORM\Column(type="string", length=128, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(max=120)
     */
    private string $issuerName;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Assert\Length(max=120)
     */
    private ?string $representativeName = null;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\Length(min=9, max=9)
     */
    private ?string $representativeId = null;

    /**
     * @ORM\Column(type="string", length=8, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice(callback={InvoiceType::class, "getValidValues"})
     */
    private string $type;

    /**
     * @ORM\Column(type="string", length=8, nullable=true)
     * @Assert\Choice(callback={CorrectiveType::class, "getValidValues"})
     */
    private ?string $correctiveType = null;

    /**
     * @ORM\Column(type="string", length=512, nullable=false)
     * @Assert\Length(max=500)
     * @Assert\NotBlank()
     */
    private string $description = '';

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=false)
     * @Assert\NotNull()
     */
    private float $totalTaxAmount = 0.00;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=false)
     * @Assert\NotNull()
     */
    private float $totalAmount = 0.00;

    /** @ORM\Column(type="decimal", scale=2, nullable=true) */
    private ?float $correctedTaxAmount = null;

    /** @ORM\Column(type="decimal", scale=2, nullable=true) */
    private ?float $correctedBaseAmount = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^[0-9A-F]{64}$/")
     */
    private string $hash = '';

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Assert\Regex(pattern="/^[0-9A-F]{64}$/")
     */
    private ?string $previousHash = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=false)
     * @Assert\NotBlank()
     */
    private DateTimeInterface $hashedAt;

    /** @ORM\Column(type="boolean", nullable=false, options={"default"=false}) */
    private bool $isSent = false;

    /** @ORM\Column(type="datetime_immutable", nullable=true) */
    private ?DateTimeInterface $createdAt = null;

    /** @ORM\Column(type="datetime_immutable", nullable=true) */
    private ?DateTimeInterface $updatedAt = null;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(name="previous_invoice_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private ?Invoice $previousInvoice = null;

    /**
     * @var InvoiceRecipient[]|Collection
     *
     * @ORM\OneToMany(targetEntity="InvoiceRecipient", mappedBy="invoice", cascade={"persist"})
     * @Assert\Valid()
     * @Assert\Count(max=12)
     */
    private Collection $recipients;

    /**
     * @var InvoiceLine[]|Collection
     *
     * @ORM\OneToMany(targetEntity="InvoiceLine", mappedBy="invoice", cascade={"persist"})
     * @Assert\Valid()
     * @Assert\Count(max=1000)
     */
    private Collection $lines;

    /**
     * @var Invoice[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Invoice", inversedBy="correctedBy")
     * @ORM\JoinTable(
     *     name="verifactu_invoice_corrective",
     *     joinColumns={
     *         @ORM\JoinColumn(name="invoice_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="corrective_id", referencedColumnName="id", unique=true, onDelete="CASCADE"),
     *     },
     * )
     * @ORM\OrderBy({"createdAt": "ASC"})
     */
    public Collection $correctedInvoices;

    /**
     * @var Invoice[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Invoice", mappedBy="correctedInvoices")
     * @ORM\OrderBy({"createdAt": "ASC"})
     */
    public Collection $correctedBy;

    /** @throws InvalidInvoiceException */
    public function __construct(
        string $number,
        DateTimeInterface $date,
        string $issuerId,
        string $issuerName,
        string $type = InvoiceType::INVOICE
    ) {
        $this->number = $number;
        try {
            $this->date = DateTimeImmutable::createFromFormat('Y-m-d', $date->format('Y-m-d'));
        } catch (Throwable $e) {
            throw $this->createInvalidDateException($date->format('Y-m-d'), $e);
        }
        $this->issuerId = $issuerId;
        $this->issuerName = $issuerName;
        $this->type = $type;
        $this->hashedAt = new DateTimeImmutable();
        $this->recipients = new ArrayCollection();
        $this->lines = new ArrayCollection();
        $this->correctedInvoices = new ArrayCollection();
        $this->correctedBy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    public function getCsv(): ?string
    {
        return $this->csv;
    }

    public function setCsv(?string $csv): void
    {
        $this->csv = $csv;
    }

    public function getPreviousNumber(): ?string
    {
        return $this->previousNumber;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getPreviousDate(): ?DateTimeInterface
    {
        return $this->previousDate;
    }

    public function getIssuerId(): string
    {
        return $this->issuerId;
    }

    public function getIssuerName(): string
    {
        return $this->issuerName;
    }

    public function getPreviousIssuerId(): ?string
    {
        return $this->previousIssuerId;
    }

    public function getRepresentativeName(): ?string
    {
        return $this->representativeName;
    }

    public function getRepresentativeId(): ?string
    {
        return $this->representativeId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getCorrectiveType(): ?string
    {
        return $this->correctiveType;
    }

    public function setCorrectiveType(?string $correctiveType): void
    {
        $this->correctiveType = $correctiveType;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getTotalTaxAmount(): float
    {
        return $this->totalTaxAmount;
    }

    public function setTotalTaxAmount(float $totalTaxAmount): void
    {
        $this->totalTaxAmount = $totalTaxAmount;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getCorrectedTaxAmount(): ?float
    {
        return $this->correctedTaxAmount;
    }

    public function setCorrectedTaxAmount(?float $correctedTaxAmount): void
    {
        $this->correctedTaxAmount = $correctedTaxAmount;
    }

    public function getCorrectedBaseAmount(): ?float
    {
        return $this->correctedBaseAmount;
    }

    public function setCorrectedBaseAmount(?float $correctedBaseAmount): void
    {
        $this->correctedBaseAmount = $correctedBaseAmount;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getPreviousHash(): ?string
    {
        return $this->previousHash;
    }

    public function getHashedAt(): DateTimeInterface
    {
        return $this->hashedAt;
    }

    public function setHashedAt(DateTimeInterface $hashedAt): void
    {
        $this->hashedAt = $hashedAt;
    }

    public function getIsSent(): bool
    {
        return $this->isSent;
    }

    public function setIsSent(bool $isSent): void
    {
        $this->isSent = $isSent;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /** @return InvoiceRecipient[] */
    public function getRecipients(): array
    {
        return $this->recipients->toArray();
    }

    public function addRecipient(InvoiceRecipient $recipient): void
    {
        if ($this->recipients->contains($recipient)) {
            return;
        }

        $this->recipients->add($recipient);
        $recipient->setInvoice($this);
    }

    public function removeRecipient(InvoiceRecipient $recipient): void
    {
        if (!$this->recipients->contains($recipient)) {
            return;
        }

        $this->recipients->removeElement($recipient);
        $recipient->setInvoice(null);
    }

    /** @return InvoiceLine[] */
    public function getLines(): array
    {
        return $this->lines->toArray();
    }

    public function addLine(InvoiceLine $line): void
    {
        $foundLine = $this->findLineByType($line);
        if (!$foundLine) {
            $this->lines->add($line);
            $line->setInvoice($this);

            return;
        }

        $foundLine->setBaseAmount(round($foundLine->getBaseAmount() + $line->getBaseAmount(), 2));
        $foundLine->setTaxAmount(round($foundLine->getTaxAmount() + $line->getTaxAmount(), 2));
    }

    /** @return Invoice[] */
    public function getCorrectedInvoices(): array
    {
        return $this->correctedInvoices->toArray();
    }

    public function addCorrectedInvoice(Invoice $invoice): void
    {
        if ($this->correctedInvoices->contains($invoice)) {
            return;
        }

        $this->correctedInvoices->add($invoice);
        $invoice->addCorrectedBy($this);
    }

    public function removeCorrectedInvoice(Invoice $invoice): void
    {
        if (!$this->correctedInvoices->contains($invoice)) {
            return;
        }

        $this->correctedInvoices->removeElement($invoice);
    }

    /** @return Invoice[] */
    public function getCorrectedBy(): array
    {
        return $this->correctedBy->toArray();
    }

    public function addCorrectedBy(Invoice $invoice): void
    {
        if ($this->correctedBy->contains($invoice)) {
            return;
        }

        $this->correctedBy->add($invoice);
        $invoice->addCorrectedInvoice($this);
    }

    public function removeCorrectedBy(Invoice $invoice): void
    {
        if (!$this->correctedBy->contains($invoice)) {
            return;
        }

        $this->correctedBy->removeElement($invoice);
        $invoice->removeCorrectedBy($this);
    }

    public function getPreviousInvoice(): ?Invoice
    {
        return $this->previousInvoice;
    }

    public function setPreviousInvoice(?Invoice $previousInvoice): void
    {
        $this->previousInvoice = $previousInvoice;
        $this->previousHash = $previousInvoice ? $previousInvoice->getHash() : null;
        $this->previousNumber = $previousInvoice ? $previousInvoice->getNumber() : null;
        $this->previousDate = $previousInvoice ? $previousInvoice->getDate() : null;
        $this->previousIssuerId = $previousInvoice ? $previousInvoice->getIssuerId() : null;
    }

    public function setRepresentative(string $id, string $name): void
    {
        $this->representativeId = $id;
        $this->representativeName = $name;
    }

    public function clearRepresentative(): void
    {
        $this->representativeId = null;
        $this->representativeName = null;
    }

    private function findLineByType(InvoiceLine $line): ?InvoiceLine
    {
        $matches = array_values(
            array_filter($this->lines->toArray(), static function (?InvoiceLine $existingLine) use ($line): bool {
                if (!$existingLine) {
                    return false;
                }

                return round($line->getTaxRate(), 2) === round($existingLine->getTaxRate(), 2)
                    && $line->getTaxType() === $existingLine->getTaxType()
                    && $line->getRegimeType() === $existingLine->getRegimeType()
                    && $line->getOperationType() === $existingLine->getOperationType();
            })
        );

        return (isset($matches[0]) && $matches[0] instanceof InvoiceLine) ? $matches[0] : null;
    }

    /** @Assert\Callback() */
    public function validatePreviousInvoiceData(ExecutionContextInterface $context): void
    {
        if ($this->previousNumber && !$this->previousHash) {
            $context->buildViolation('Previous hash is required if previous invoice ID is provided')
                ->atPath('previousHash')
                ->addViolation();

            return;
        }

        if ($this->previousHash !== null && $this->previousNumber === null) {
            $context->buildViolation('Previous invoice ID is required if previous hash is provided')
                ->atPath('previousNumber')
                ->addViolation();
        }
    }

    /** @Assert\Callback() */
    final public function validateTotals(ExecutionContextInterface $context): void
    {
        $calculatedTotalTaxAmount = 0;
        $calculatedTotalBaseAmount = 0;
        foreach ($this->lines as $line) {
            $calculatedTotalTaxAmount += $line->getTaxAmount();
            $calculatedTotalBaseAmount += $line->getBaseAmount();
        }

        if (round($this->totalTaxAmount, 2) !== round($calculatedTotalTaxAmount, 2)) {
            $context->buildViolation('Calculated total tax amount was {{ calculated }}, {{ expected }} expected')
                ->setParameter('{{ calculated }}', $calculatedTotalTaxAmount)
                ->setParameter('{{ expected }}', $this->totalTaxAmount)
                ->atPath('totalTaxAmount')
                ->addViolation();
        }

        $calculatedTotalAmount = $calculatedTotalBaseAmount + $calculatedTotalTaxAmount;
        $diff = abs($calculatedTotalAmount - $this->getTotalAmount());
        if (round($diff, 2) <= round(0.01, 2)) {
            return;
        }

        $context->buildViolation('Calculated total amount was {{ calculated }}, {{ expected }} expected')
            ->setParameter('{{ calculated }}', $calculatedTotalAmount)
            ->setParameter('{{ expected }}', $this->totalAmount)
            ->atPath('totalAmount')
            ->addViolation();
    }

    /** @Assert\Callback() */
    final public function validateRecipients(ExecutionContextInterface $context): void
    {
        $hasRecipients = $this->recipients->count() > 0;
        if (InvoiceType::isSimplified($this->type)) {
            if ($hasRecipients) {
                $context->buildViolation('Simplified invoices cannot have recipients')
                    ->atPath('recipients')
                    ->addViolation();
            }

            return;
        }

        if (!$hasRecipients) {
            $context->buildViolation('Non-simplified invoices must have at least one recipient')
                ->atPath('recipients')
                ->addViolation();
        }
    }

    /** @Assert\Callback() */
    public function validateCorrectiveInvoice(ExecutionContextInterface $context): void
    {
        $isCorrective = InvoiceType::isCorrective($this->getType());

        switch (true) {
            case $isCorrective && !$this->correctiveType:
                $context->buildViolation('This invoice is corrective, and it needs a corrective type.')
                    ->atPath('correctiveType')
                    ->addViolation();
                break;
            case !$isCorrective && $this->correctiveType:
                $context->buildViolation('This type of invoice cannot have a corrective type.')
                    ->atPath('correctiveType')
                    ->addViolation();
                break;
            case !$isCorrective && $this->correctedInvoices->count() > 0:
                $context->buildViolation('This type of invoice cannot have corrected invoices.')
                    ->atPath('correctedInvoices')
                    ->addViolation();
                break;
            case CorrectiveType::SUBSTITUTION === $this->correctiveType && null === $this->correctedBaseAmount:
                $context->buildViolation('Missing corrected base amount for corrective invoice by substitution.')
                    ->atPath('correctedBaseAmount')
                    ->addViolation();
                break;
            case CorrectiveType::SUBSTITUTION === $this->correctiveType && null === $this->correctedTaxAmount:
                $context->buildViolation('Missing corrected tax amount for corrective invoice by substitution.')
                    ->atPath('correctedTaxAmount')
                    ->addViolation();
                break;
            case CorrectiveType::SUBSTITUTION !== $this->correctiveType && null !== $this->correctedBaseAmount:
                $context->buildViolation('This invoice cannot have a corrected base amount.')
                    ->atPath('correctedBaseAmount')
                    ->addViolation();
                break;
            case CorrectiveType::SUBSTITUTION !== $this->correctiveType && null !== $this->correctedTaxAmount:
                $context->buildViolation('This invoice cannot have a corrected tax amount.')
                    ->atPath('correctedTaxAmount')
                    ->addViolation();
                break;
            default:
                break;
        }
    }

    private function createInvalidDateException(string $date, Throwable $previous): InvalidInvoiceException
    {
        $dateError = new Assert\Date();
        $violation = new ConstraintViolation($dateError->message, $dateError->message, [], '', 'date', $date);

        return new InvalidInvoiceException($this, new ConstraintViolationList([$violation]), $previous);
    }
}
