<?php

namespace App\Application\DTO;

readonly class DisbursementCalculatorRequest
{
    public function __construct(
        public \DateTimeImmutable $calculationDate,
        public string $merchantReference,
    ) {
    }
}
