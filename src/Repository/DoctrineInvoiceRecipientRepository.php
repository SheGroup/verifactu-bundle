<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use SheGroup\VerifactuBundle\Entity\InvoiceRecipient;

final class DoctrineInvoiceRecipientRepository extends ServiceEntityRepository implements
    InvoiceRecipientRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceRecipient::class);
    }

    public function findById(int $id): ?InvoiceRecipient
    {
        $recipient = $this->find($id);

        return $recipient instanceof InvoiceRecipient ? $recipient : null;
    }

    /** @throws ORMException */
    public function save(InvoiceRecipient $recipient): void
    {
        $this->getEntityManager()->persist($recipient);
        $this->getEntityManager()->flush();
    }

    /** @throws ORMException */
    public function remove(InvoiceRecipient $recipient): void
    {
        $this->getEntityManager()->remove($recipient);
        $this->getEntityManager()->flush();
    }
}
