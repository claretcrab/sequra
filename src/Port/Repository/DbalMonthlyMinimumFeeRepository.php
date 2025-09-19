<?php

namespace App\Port\Repository;

use App\Domain\Exception\MonthlyMinimumFeeExistsException;
use App\Domain\MonthlyMinimumFee;
use App\Domain\MonthlyMinimumFeeRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class DbalMonthlyMinimumFeeRepository implements MonthlyMinimumFeeRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function save(MonthlyMinimumFee $monthlyMinimumFee): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert('monthly_minimum_fees')
            ->values([
                'id' => ':id',
                'merchant_reference' => ':merchant_reference',
                'fee' => ':fee',
                'created_at' => ':created_at',
            ])
            ->setParameter('id', $monthlyMinimumFee->id()->toString())
            ->setParameter('merchant_reference', $monthlyMinimumFee->merchantReference())
            ->setParameter('fee', $monthlyMinimumFee->fee())
            ->setParameter('created_at', $monthlyMinimumFee->createdAt()->format('Y-m-01'));
        try {
            $qb->executeStatement();
        } catch (UniqueConstraintViolationException $e) {
            throw new MonthlyMinimumFeeExistsException('Monthly minimum fee already exists for this merchant and date.', 0, $e);
        }
    }
}
