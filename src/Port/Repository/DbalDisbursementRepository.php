<?php

namespace App\Port\Repository;

use App\Domain\Disbursement;
use App\Domain\DisbursementRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Uid\Uuid;

class DbalDisbursementRepository implements DisbursementRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function save(Disbursement $disbursement): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert('disbursements')
            ->values([
                'id' => ':id',
                'amount' => ':amount',
                'fee' => ':fee',
                'merchant_reference' => ':merchant_reference',
                'disbursed_at' => ':disbursed_at',
            ])
            ->setParameter('id', $disbursement->id()->toString())
            ->setParameter('amount', $disbursement->amount())
            ->setParameter('fee', $disbursement->fee())
            ->setParameter('merchant_reference', $disbursement->merchantReference())
            ->setParameter('disbursed_at', $disbursement->disbursedAt()->format('Y-m-d'));
        $qb->executeStatement();
    }

    public function findById(Uuid $id): ?Disbursement
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('*')
            ->from('disbursements')
            ->where('id = :id')
            ->setParameter('id', $id->toString());

        $result = $qb->executeQuery()->fetchAssociative();

        if (!$result) {
            return null;
        }

        return new Disbursement(
            Uuid::fromString($result['id']),
            $result['amount'],
            $result['fee'],
            $result['merchant_reference'],
            new \DateTimeImmutable($result['disbursed_at'])
        );
    }

    public function getStatistics(): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('date_trunc(\'year\', disbursed_at) as year, COUNT(1) as total_number, SUM(amount) as total_amount, SUM(fee) as total_fee')
            ->from('disbursements')
            ->orderBy('year', 'ASC')
            ->groupBy('year');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getMonthlyStatistics(\DateTimeImmutable $disbursedAt): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('merchant_reference,
    DATE_TRUNC(\'month\', disbursed_at) AS disbursement_month,
    COUNT(*) AS total_disbursements,
    SUM(amount) AS total_amount,
    SUM(fee) AS total_fee')
            ->from('disbursements')
            ->where('disbursed_at = :disbursedAt')
            ->groupBy('merchant_reference, disbursement_month')
            ->setParameter('disbursedAt', $disbursedAt->format('Y-m-01'));

        return $qb->executeQuery()->fetchAllAssociative();
    }
}
