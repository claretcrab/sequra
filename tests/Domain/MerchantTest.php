<?php

namespace App\Tests\Domain;

use App\Domain\DisbursementFrequency;
use App\Domain\Merchant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class MerchantTest extends TestCase
{
    #[DataProvider('disbursementProvider')]
    public function testIsNotEligibleForDisbursement(DisbursementFrequency $disbursementFrequency, \DateTimeImmutable $liveOn, \DateTimeImmutable $calculationDate, $expected): void
    {
        $merchant = new Merchant(
            id: new Uuid('2ae89f6d-e210-4993-b4d1-0bd2d279da62'),
            reference: 'treutel_schumm_fadel',
            email: 'info@treutel-schumm-and-fadel.com',
            liveOn: $liveOn,
            disbursementFrequency: $disbursementFrequency,
            minimumMonthlyFee: 2900
        );

        $this->assertEquals($expected, $merchant->isNotEligibleForDisbursement($calculationDate));
    }

    public static function disbursementProvider(): array
    {
        return [
            'eligible daily frequency' => [DisbursementFrequency::DAILY, new \DateTimeImmutable('2022-01-01'), new \DateTimeImmutable('2022-01-01'), false],
            'eligible weekly frequency' => [DisbursementFrequency::WEEKLY, new \DateTimeImmutable('2024-01-01'), new \DateTimeImmutable('2024-01-08'), false],
            'not eligible weekly frequency' => [DisbursementFrequency::WEEKLY, new \DateTimeImmutable('2022-01-01'), new \DateTimeImmutable('2022-01-02'), true],
        ];
    }

    #[DataProvider('monthlyMinimumFeeProvider')]
    public function testIsNotEligibleForMonthlyMinimumFee(int $minimumMonthlyFee, int $monthlyFee, $expected): void
    {
        $merchant = new Merchant(
            id: new Uuid('2ae89f6d-e210-4993-b4d1-0bd2d279da62'),
            reference: 'treutel_schumm_fadel',
            email: 'info@treutel-schumm-and-fadel.com',
            liveOn: new \DateTimeImmutable('2022-01-01'),
            disbursementFrequency: DisbursementFrequency::DAILY,
            minimumMonthlyFee: $minimumMonthlyFee
        );

        $this->assertEquals($expected, $merchant->isNotEligibleForMonthlyMinimumFee($monthlyFee));
    }

    public static function monthlyMinimumFeeProvider(): array
    {
        return [
            'eligible' => [2900, 2800, false],
            'not eligible' => [0, 2800, true],
        ];
    }
}
