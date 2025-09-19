<?php

namespace App\Tests\Application;

use App\Application\DTO\MonthlyMinimumFeeCalculatorRequest;
use App\Application\MonthlyMinimumFeeCalculator;
use App\Domain\Merchant;
use App\Domain\MerchantRepository;
use App\Domain\MonthlyMinimumFee;
use App\Domain\MonthlyMinimumFeeRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MonthlyMinimumFeeCalculatorTest extends TestCase
{
    private MerchantRepository $merchantRepository;
    private MonthlyMinimumFeeRepository $monthlyMinimumFeeRepository;
    private LoggerInterface $logger;
    private MonthlyMinimumFeeCalculator $calculator;

    protected function setUp(): void
    {
        $this->merchantRepository = $this->createMock(MerchantRepository::class);
        $this->monthlyMinimumFeeRepository = $this->createMock(MonthlyMinimumFeeRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->calculator = new MonthlyMinimumFeeCalculator(
            $this->merchantRepository,
            $this->monthlyMinimumFeeRepository,
            $this->logger
        );
    }

    public function testMerchantNotFound(): void
    {
        $this->merchantRepository
            ->method('findByReference')
            ->willReturn(null);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Merchant not found: merchant-1');

        $this->calculator->calculate(
            new MonthlyMinimumFeeCalculatorRequest(new \DateTimeImmutable('2024-01-01'), 'merchant-1', 100)
        );
    }

    public function testMerchantNotEligible(): void
    {
        $merchant = $this->createMock(Merchant::class);
        $merchant->method('isNotEligibleForMonthlyMinimumFee')->willReturn(true);

        $this->merchantRepository
            ->method('findByReference')
            ->willReturn($merchant);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Merchant not eligible: merchant-1');

        $this->calculator->calculate(
            new MonthlyMinimumFeeCalculatorRequest(new \DateTimeImmutable('2024-01-01'), 'merchant-1', 150)
        );
    }

    public function testSuccessfulCalculation(): void
    {
        $merchant = $this->createMock(Merchant::class);
        $merchant->method('isNotEligibleForMonthlyMinimumFee')->willReturn(false);
        $merchant->method('minimumMonthlyFee')->willReturn(200);

        $this->merchantRepository
            ->method('findByReference')
            ->willReturn($merchant);

        $this->monthlyMinimumFeeRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (MonthlyMinimumFee $fee) {
                return 'merchant-1' === $fee->merchantReference()
                    && 100 === $fee->fee()
                    && '2024-01-01' === $fee->createdAt()->format('Y-m-d');
            }));

        $this->calculator->calculate(
            new MonthlyMinimumFeeCalculatorRequest(new \DateTimeImmutable('2024-01-01'), 'merchant-1', 100)
        );
    }
}
