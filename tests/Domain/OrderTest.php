<?php

namespace App\Tests\Domain;

use App\Domain\DisbursementStatus;
use App\Domain\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    #[DataProvider('feeProvider')]
    public function testFee($amount, $expectedFee): void
    {
        $order = new Order(
            id: '056d024481a9',
            merchantReference: 'treutel_schumm_fadel',
            amount: $amount,
            createdAt: new \DateTimeImmutable('2023-01-01'),
            disbursementStatus: DisbursementStatus::PENDING,
        );

        $this->assertEquals($expectedFee, $order->fee());
    }

    public static function feeProvider(): array
    {
        return [
            'order amount below 50' => [1000, 10],
            'order amount between 50 and 300' => [10000, 95],
            'order amount above 300' => [100000, 850],
            'order amount with round up' => [1099, 11],
        ];
    }
}
