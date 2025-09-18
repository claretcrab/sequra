<?php

namespace App\Domain;

use Symfony\Component\Uid\Uuid;

interface OrderRepository
{
    public function save(Order $order): void;

    public function findById(string $id): ?Order;

    public function findOrdersWithoutDisbursementByMerchant(string $merchantReference): array;

    public function markOrdersAsDisbursed(string $merchantReference, string $date, Uuid $disbursementId): void;
}
