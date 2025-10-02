<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use SheGroup\VerifactuBundle\Entity\InvoiceLine;

final class DoctrineInvoiceLineRepository extends ServiceEntityRepository implements InvoiceLineRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceLine::class);
    }

    public function findById(int $id): ?InvoiceLine
    {
        $line = $this->find($id);

        return $line instanceof InvoiceLine ? $line : null;
    }

    /** @throws ORMException */
    public function save(InvoiceLine $line): void
    {
        $this->getEntityManager()->persist($line);
        $this->getEntityManager()->flush();
    }

    /** @throws ORMException */
    public function remove(InvoiceLine $line): void
    {
        $this->getEntityManager()->remove($line);
        $this->getEntityManager()->flush();
    }
}
