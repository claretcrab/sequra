<?php

namespace App\Domain;

use Symfony\Component\Uid\Uuid;

class MonthlyMinimumFee
{
    public function __construct(
        private Uuid $id,
        private string $merchantReference,
        private int $fee,
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function merchantReference(): string
    {
        return $this->merchantReference;
    }

    public function fee(): int
    {
        return $this->fee;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
