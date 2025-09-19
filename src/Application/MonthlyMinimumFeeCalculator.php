<?php

namespace App\Application;

use App\Application\DTO\MonthlyMinimumFeeCalculatorRequest;
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

    public function calculate(MonthlyMinimumFeeCalculatorRequest $request): void
    {
        $merchant = $this->merchantRepository->findByReference($request->merchantReference);

        if (null === $merchant) {
            $this->logger->error('Merchant not found: '.$request->merchantReference);

            return;
        }

        if ($merchant->isNotEligibleForMonthlyMinimumFee($request->fee)) {
            $this->logger->info('Merchant not eligible: '.$request->merchantReference);

            return;
        }

        $monthlyMinimumFee = new MonthlyMinimumFee(
            id: Uuid::v7(),
            merchantReference: $request->merchantReference,
            fee: $merchant->minimumMonthlyFee() - $request->fee,
            createdAt: $request->calculationDate,
        );
        try {
            $this->monthlyMinimumFeeRepository->save($monthlyMinimumFee);
        } catch (MonthlyMinimumFeeExistsException $e) {
            $this->logger->warning('Monthly minimum fee already exists for merchant: '.$request->merchantReference);
        }
    }
}
