<?php

namespace App\Application;

use App\Application\DTO\DisbursementCalculatorRequest;
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

    public function calculate(DisbursementCalculatorRequest $request): void
    {
        $merchant = $this->merchantRepository->findByReference($request->merchantReference);

        if (null === $merchant) {
            $this->logger->error('Merchant not found: '.$request->merchantReference);

            return;
        }

        if ($merchant->isNotEligibleForDisbursement($request->calculationDate)) {
            $this->logger->info('Merchant not eligible: '.$request->merchantReference);

            return;
        }

        $orderAggregate = $this->orderRepository->findOrdersWithoutDisbursementByMerchant(
            merchantReference: $request->merchantReference,
            createdAt: $request->calculationDate,
        );
        if (!isset($orderAggregate['total_amount'], $orderAggregate['total_fee'])) {
            $this->logger->warning('No orders found for merchant: '.$request->merchantReference);

            return;
        }

        $disbursementId = Uuid::v7();

        $disbursement = new Disbursement(
            id: $disbursementId,
            amount: $orderAggregate['total_amount'],
            fee: $orderAggregate['total_fee'],
            merchantReference: $request->merchantReference,
            disbursedAt: $request->calculationDate,
        );

        try {
            $this->connection->beginTransaction();

            $this->disbursementRepository->save($disbursement);

            $this->orderRepository->markOrdersAsDisbursed(
                merchantReference: $request->merchantReference,
                createdAt: $request->calculationDate,
                disbursementId: $disbursementId,
            );

            $this->connection->commit();
        } catch (\Exception $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }
            $this->logger->error('Failed transaction: '.$e->getMessage());

            return;
        }
    }
}
