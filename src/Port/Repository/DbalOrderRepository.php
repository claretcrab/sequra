<?php

namespace App\Port\Repository;

use App\Domain\DisbursementStatus;
use App\Domain\Order;
use App\Domain\OrderRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class DbalOrderRepository implements OrderRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function save(Order $order): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert('orders')
            ->values([
                'id' => ':id',
                'merchant_reference' => ':merchant_reference',
                'amount' => ':amount',
                'created_at' => ':created_at',
                'disbursement_status' => ':disbursement_status',
            ])
            ->setParameter('id', $order->id())
            ->setParameter('merchant_reference', $order->merchantReference())
            ->setParameter('amount', $order->amount())
            ->setParameter('created_at', $order->createdAt()->format('Y-m-d'))
            ->setParameter('disbursement_status', $order->disbursementStatus()->value);
        $qb->executeStatement();
    }

    public function findById(string $id): ?Order
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('*')
            ->from('orders')
            ->where('id = :id')
            ->setParameter('id', $id);

        $result = $qb->executeQuery()->fetchAssociative();

        if (!$result) {
            return null;
        }

        return new Order(
            $result['id'],
            $result['merchant_reference'],
            $result['amount'],
            new \DateTimeImmutable($result['created_at']),
            DisbursementStatus::from($result['disbursement_status']),
        );
    }

    public function findMerchantsWithoutDisbursement(): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('DISTINCT merchant_reference')
            ->from('orders')
            ->where('disbursement_status = :status')
            ->setParameter('status', DisbursementStatus::PENDING->value);

        return $qb->executeQuery()->fetchFirstColumn();
    }


    public function findOrdersWithoutDisbursementByMerchant(string $merchantReference): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('*')
            ->from('orders')
            ->where('disbursement_status = :status')
            ->andWhere('merchant_reference = :merchant_reference')
            ->setParameter('status', DisbursementStatus::PENDING->value);
        $qb->setParameter('merchant_reference', $merchantReference);

        $results = $qb->executeQuery()->fetchAllAssociative();

        $orders = [];
        foreach ($results as $result) {
            $orders[] = new Order(
                $result['id'],
                $result['merchant_reference'],
                $result['amount'],
                new \DateTimeImmutable($result['created_at']),
                DisbursementStatus::from($result['disbursement_status']),
            );
        }

        return $orders;
    }

}
