<?php

namespace App\Domain;

use Symfony\Component\Uid\Uuid;

class Disbursement
{

    public function __construct(
        private Uuid $id,
        private int $amount,
        private int $fee,
    ) {
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function fee(): int
    {
        return $this->fee;
    }
}
