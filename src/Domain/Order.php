<?php

namespace App\Domain;

use Symfony\Component\Uid\Uuid;

class Order
{
    public function __construct(
        private string $id,
        private string $merchantReference,
        private int $amount,
        private \DateTimeImmutable $createdAt,
        private DisbursementStatus $disbursementStatus = DisbursementStatus::PENDING,
        private ?Uuid $disbursementId = null,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function merchantReference(): string
    {
        return $this->merchantReference;
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function disbursementStatus(): DisbursementStatus
    {
        return $this->disbursementStatus;
    }

    public function disbursementId(): ?Uuid
    {
        return $this->disbursementId;
    }
}
