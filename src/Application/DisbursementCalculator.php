<?php

namespace App\Application;

use App\Domain\Disbursement;
use App\Domain\DisbursementFrequency;
use App\Domain\DisbursementRepository;
use App\Domain\MerchantRepository;
use App\Domain\OrderRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class DisbursementCalculator
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly MerchantRepository $merchantRepository,
        private readonly DisbursementRepository $disbursementRepository,
        private readonly LoggerInterface $logger,
    ) {}

    public function calculate(\DateTimeImmutable $calculationDate, string $merchantReference): void
    {
        $merchant = $this->merchantRepository->findByReference($merchantReference);

        if ($merchant === null) {
            $this->logger->error('Merchant not found: ' . $merchantReference);
            return;
        }

        if ($this->isNotEligible($merchant, $calculationDate)) {
            $this->logger->info('Merchant not eligible: ' . $merchantReference);
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
        //TODO: transaction
        $this->disbursementRepository->save($disbursement);

        $this->orderRepository->markOrdersAsDisbursed(
            merchantReference: $merchantReference,
            createdAt: $calculationDate,
            disbursementId: $disbursementId,
        );
    }

    /**
     * @param \App\Domain\Merchant $merchant
     * @param \DateTimeImmutable $calculationDate
     * @return bool
     */
    public function isNotEligible(\App\Domain\Merchant $merchant, \DateTimeImmutable $calculationDate): bool
    {
        return $merchant->disbursementFrequency() === DisbursementFrequency::WEEKLY &&
            $merchant->liveOn()->format('N') !== $calculationDate->format('N');
    }
}
