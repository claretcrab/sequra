<?php

namespace App\Application\DTO;

readonly class MonthlyMinimumFeeCalculatorRequest
{
    public function __construct(
        public \DateTimeImmutable $calculationDate,
        public string $merchantReference,
        public int $fee,
    ) {
    }
}
