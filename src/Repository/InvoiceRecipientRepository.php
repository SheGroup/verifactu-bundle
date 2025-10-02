<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Repository;

use Doctrine\ORM\ORMException;
use SheGroup\VerifactuBundle\Entity\InvoiceRecipient;

interface InvoiceRecipientRepository
{
    public function findById(int $id): ?InvoiceRecipient;

    /** @throws ORMException */
    public function save(InvoiceRecipient $recipient): void;

    /** @throws ORMException */
    public function remove(InvoiceRecipient $recipient): void;
}
