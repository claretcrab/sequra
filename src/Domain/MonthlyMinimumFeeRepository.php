<?php

namespace App\Domain;

interface MonthlyMinimumFeeRepository
{
    public function save(MonthlyMinimumFee $monthlyMinimumFee): void;
}
