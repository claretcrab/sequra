<?php

namespace App\Domain;

use Symfony\Component\Uid\Uuid;

interface OrderRepository
{
    public function save(Order $order): void;

    public function findById(string $id): ?Order;

    public function findMerchantsWithoutDisbursement(\DateTimeImmutable $createdAt): array;

    public function findOrdersWithoutDisbursementByMerchant(string $merchantReference, \DateTimeImmutable $createdAt): array;

    public function markOrdersAsDisbursed(string $merchantReference, \DateTimeImmutable $createdAt, Uuid $disbursementId): void;
}
