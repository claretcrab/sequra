<?php

namespace App\Application;

use App\Domain\Disbursement;
use App\Domain\DisbursementRepository;
use App\Domain\MerchantRepository;
use App\Domain\OrderRepository;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class DisbursementCalculator
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly MerchantRepository $merchantRepository,
        private readonly DisbursementRepository $disbursementRepository,
        private readonly LoggerInterface $logger,
        private readonly Connection $connection,
    ) {
    }

    public function calculate(\DateTimeImmutable $calculationDate, string $merchantReference): void
    {
        $merchant = $this->merchantRepository->findByReference($merchantReference);

        if (null === $merchant) {
            $this->logger->error('Merchant not found: '.$merchantReference);

            return;
        }

        if ($merchant->isNotEligibleForDisbursement($calculationDate)) {
            $this->logger->info('Merchant not eligible: '.$merchantReference);

            return;
        }

        $orderAggregate = $this->orderRepository->findOrdersWithoutDisbursementByMerchant(
            merchantReference: $merchantReference,
            createdAt: $calculationDate,
        );

        $disbursementId = Uuid::v7();

        $disbursement = new Disbursement(
            id: $disbursementId,
            amount: $orderAggregate['total_amount'],
            fee: $orderAggregate['total_fee'],
            merchantReference: $merchantReference,
            disbursedAt: $calculationDate,
        );

        try {
            $this->connection->beginTransaction();

            $this->disbursementRepository->save($disbursement);

            $this->orderRepository->markOrdersAsDisbursed(
                merchantReference: $merchantReference,
                createdAt: $calculationDate,
                disbursementId: $disbursementId,
            );
        } catch (\Exception $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }
            $this->logger->error('Failed transaction: '.$e->getMessage());

            return;
        }
    }
}
