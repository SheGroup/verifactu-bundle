<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Factory;

use SheGroup\VerifactuBundle\Entity\InvoiceRecipient;
use SheGroup\VerifactuBundle\Exception\InvalidInvoiceRecipientException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class InvoiceRecipientFactory
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /** @throws InvalidInvoiceRecipientException */
    public function create(
        string $id,
        string $name,
        string $idType = InvoiceRecipient::DEFAULT_ID_TYPE,
        string $country = InvoiceRecipient::LOCAL_COUNTRY
    ): InvoiceRecipient {
        $recipient = new InvoiceRecipient();
        $recipient->setRecipientId($id);
        $recipient->setName($name);
        $recipient->setIdType($idType);
        $recipient->setCountry($country);

        $errors = $this->validator->validate($recipient);
        if (count($errors) > 0) {
            throw new InvalidInvoiceRecipientException($recipient, $errors);
        }

        return $recipient;
    }
}
