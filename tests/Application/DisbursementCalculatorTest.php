<?php

namespace App\Tests\Application;

use App\Application\DisbursementCalculator;
use App\Domain\Disbursement;
use App\Domain\DisbursementRepository;
use App\Domain\Merchant;
use App\Domain\MerchantRepository;
use App\Domain\OrderRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DisbursementCalculatorTest extends TestCase
{
    private OrderRepository $orderRepository;
    private MerchantRepository $merchantRepository;
    private DisbursementRepository $disbursementRepository;
    private LoggerInterface $logger;
    private DisbursementCalculator $calculator;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->merchantRepository = $this->createMock(MerchantRepository::class);
        $this->disbursementRepository = $this->createMock(DisbursementRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->calculator = new DisbursementCalculator(
            $this->orderRepository,
            $this->merchantRepository,
            $this->disbursementRepository,
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

        $this->calculator->calculate(new \DateTimeImmutable('2024-01-01'), 'merchant-1');
    }

    public function testMerchantNotEligible(): void
    {
        $merchant = $this->createMock(Merchant::class);
        $merchant->method('isNotEligibleForDisbursement')->willReturn(true);

        $this->merchantRepository
            ->method('findByReference')
            ->willReturn($merchant);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Merchant not eligible: merchant-1');

        $this->calculator->calculate(new \DateTimeImmutable('2024-01-02'), 'merchant-1');
    }

    public function testSuccessfulCalculation(): void
    {
        $merchant = $this->createMock(Merchant::class);
        $merchant->method('isNotEligibleForDisbursement')->willReturn(false);

        $this->merchantRepository
            ->method('findByReference')
            ->willReturn($merchant);

        $this->orderRepository
            ->method('findOrdersWithoutDisbursementByMerchant')
            ->willReturn(['total_amount' => 1000, 'total_fee' => 50]);

        $this->disbursementRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Disbursement::class));

        $this->orderRepository
            ->expects($this->once())
            ->method('markOrdersAsDisbursed');

        $this->calculator->calculate(new \DateTimeImmutable('2024-01-01'), 'merchant-1');
    }
}
