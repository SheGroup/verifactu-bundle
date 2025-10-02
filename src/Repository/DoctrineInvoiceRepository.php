<?php

declare(strict_types=1);

namespace SheGroup\VerifactuBundle\Repository;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use SheGroup\VerifactuBundle\Entity\Invoice;

final class DoctrineInvoiceRepository extends ServiceEntityRepository implements InvoiceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function findById(int $id): ?Invoice
    {
        $invoice = $this->find($id);

        return $invoice instanceof Invoice ? $invoice : null;
    }

    public function findByHash(string $hash): ?Invoice
    {
        $invoice = $this->findOneBy(['hash' => $hash], ['id' => 'DESC']);

        return $invoice instanceof Invoice ? $invoice : null;
    }

    public function findByNumber(string $number): ?Invoice
    {
        $invoice = $this->findOneBy(['number' => $number], ['id' => 'DESC']);

        return $invoice instanceof Invoice ? $invoice : null;
    }

    public function getLast(): ?Invoice
    {
        $invoice = $this->findOneBy([], ['id' => 'DESC']);

        return $invoice instanceof Invoice ? $invoice : null;
    }

    public function getPrevious(Invoice $invoice): ?Invoice
    {
        $previous = $this->createQueryBuilder('invoice')
            ->select('invoice')
            ->andWhere('invoice.id < :id')
            ->setParameter('id', $invoice->getId())
            ->orderBy('invoice.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return $previous[0] instanceof Invoice ? $previous[0] : null;
    }

    /** @return Invoice[] */
    public function getPendingToSend(int $limit = 0): array
    {
        $queryBuilder = $this->createQueryBuilder('invoice')
            ->select('invoice')
            ->andWhere('invoice.isSent = 0')
            ->orderBy('invoice.id', 'ASC');
        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /** @throws ORMException */
    public function save(Invoice $invoice): void
    {
        if (!$invoice->getCreatedAt()) {
            $invoice->setCreatedAt(new DateTimeImmutable());
        }
        $invoice->setUpdatedAt(new DateTimeImmutable());
        $this->getEntityManager()->persist($invoice);
        $this->getEntityManager()->flush();
    }

    /** @throws ORMException */
    public function remove(Invoice $invoice): void
    {
        $this->getEntityManager()->remove($invoice);
        $this->getEntityManager()->flush();
    }
}
