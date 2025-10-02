<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Repository;

use Doctrine\ORM\ORMException;
use SheGroup\VerifactuBundle\Entity\InvoiceLine;

interface InvoiceLineRepository
{
    public function findById(int $id): ?InvoiceLine;

    /** @throws ORMException */
    public function save(InvoiceLine $line): void;

    /** @throws ORMException */
    public function remove(InvoiceLine $line): void;
}
