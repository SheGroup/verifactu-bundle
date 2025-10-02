<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SheGroup\VerifactuBundle\Enum\RecipientIdType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="verifactu_invoice_recipient")
 * @ORM\Entity(repositoryClass="SheGroup\VerifactuBundle\Repository\DoctrineInvoiceRecipientRepository")
 */
class InvoiceRecipient
{
    public const LOCAL_COUNTRY = 'ES';
    public const DEFAULT_ID_TYPE = RecipientIdType::VAT;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=32, nullable=false)
     * @Assert\NotBlank()
     */
    private string $recipientId;

    /**
     * @ORM\Column(type="string", length=128, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(max=120)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice(callback={RecipientIdType::class, "getValidValues"})
     */
    private string $idType;

    /**
     * @ORM\Column(type="string", length=8, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^[A-Z]{2}$/")
     */
    private string $country;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="recipients")
     * @ORM\JoinColumn(name="invoice_id", onDelete="CASCADE")
     */
    private ?Invoice $invoice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipientId(): string
    {
        return $this->recipientId;
    }

    public function setRecipientId(string $recipientId): void
    {
        $this->recipientId = $recipientId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIdType(): string
    {
        return $this->idType;
    }

    public function setIdType(string $idType): void
    {
        $this->idType = $idType;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function isLocal(): bool
    {
        return self::LOCAL_COUNTRY === $this->getCountry();
    }

    /**
     * @Assert\Callback()
     *
     * @noinspection PhpUnused
     */
    public function validateName(ExecutionContextInterface $context): void
    {
        $constraint = $this->isLocal() ? new Assert\Length(['min' => 9, 'max' => 9]) : new Assert\Length(['max' => 20]);

        $violations = $context->getValidator()->validate($this->getRecipientId(), [$constraint]);
        foreach ($violations as $violation) {
            $context->buildViolation($violation->getMessage(), $violation->getParameters())
                ->atPath('recipientId')
                ->addViolation();
        }
    }
}
