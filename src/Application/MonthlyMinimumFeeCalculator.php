<?php

namespace App\Application;

use App\Domain\Exception\MonthlyMinimumFeeExistsException;
use App\Domain\MerchantRepository;
use App\Domain\MonthlyMinimumFee;
use App\Domain\MonthlyMinimumFeeRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class MonthlyMinimumFeeCalculator
{
    public function __construct(
        private readonly MerchantRepository $merchantRepository,
        private readonly MonthlyMinimumFeeRepository $monthlyMinimumFeeRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function calculate(\DateTimeImmutable $calculationDate, string $merchantReference, int $fee): void
    {
        $merchant = $this->merchantRepository->findByReference($merchantReference);

        if (null === $merchant) {
            $this->logger->error('Merchant not found: '.$merchantReference);

            return;
        }

        if ($this->isNotEligible($merchant, $fee)) {
            $this->logger->info('Merchant not eligible: '.$merchantReference);

            return;
        }

        $monthlyMinimumFee = new MonthlyMinimumFee(
            id: Uuid::v7(),
            merchantReference: $merchantReference,
            fee: $merchant->minimumMonthlyFee() - $fee,
            createdAt: $calculationDate,
        );
        try {
            $this->monthlyMinimumFeeRepository->save($monthlyMinimumFee);
        } catch (MonthlyMinimumFeeExistsException $e) {
            $this->logger->warning('Monthly minimum fee already exists for merchant: '.$merchantReference);
        }
    }

    public function isNotEligible(\App\Domain\Merchant $merchant, int $fee): bool
    {
        return $fee >= $merchant->minimumMonthlyFee();
    }
}
