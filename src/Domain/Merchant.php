<?php

namespace App\Domain;

use Symfony\Component\Uid\Uuid;

class Merchant
{
    public function __construct(
        private Uuid $id,
        private string $reference,
        private string $email,
        private \DateTimeImmutable $liveOn,
        private DisbursementFrequency $disbursementFrequency,
        private int $minimumMonthlyFee,
    ) {
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function reference(): string
    {
        return $this->reference;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function liveOn(): \DateTimeImmutable
    {
        return $this->liveOn;
    }

    public function disbursementFrequency(): DisbursementFrequency
    {
        return $this->disbursementFrequency;
    }

    public function minimumMonthlyFee(): int
    {
        return $this->minimumMonthlyFee;
    }

    public function isNotEligibleForDisbursement(\DateTimeImmutable $calculationDate): bool
    {
        return DisbursementFrequency::WEEKLY === $this->disbursementFrequency
            && $this->liveOn->format('N') !== $calculationDate->format('N');
    }

    public function isNotEligibleForMonthlyMinimumFee(int $fee): bool
    {
        return $fee >= $this->minimumMonthlyFee;
    }
}
