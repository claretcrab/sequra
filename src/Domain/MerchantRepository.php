<?php

namespace App\Domain;

use Symfony\Component\Uid\Uuid;

interface MerchantRepository
{
    public function save(Merchant $merchant): void;

    public function findById(Uuid $id): ?Merchant;

    public function findByReference(string $reference): ?Merchant;
}
