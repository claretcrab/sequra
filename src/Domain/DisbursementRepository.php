<?php

namespace App\Domain;

use Symfony\Component\Uid\Uuid;

interface DisbursementRepository
{
    public function save(Disbursement $disbursement): void;

    public function findById(Uuid $id): ?Disbursement;

    public function getStatistics(): array;
}
