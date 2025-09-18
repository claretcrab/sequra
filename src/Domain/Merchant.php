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
}
