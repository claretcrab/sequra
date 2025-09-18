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
            ])
            ->setParameter('id', $disbursement->id()->toString())
            ->setParameter('amount', $disbursement->amount())
            ->setParameter('fee', $disbursement->fee());
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
        );
    }
}
