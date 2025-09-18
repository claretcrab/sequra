<?php

namespace App\Domain;

enum DisbursementStatus: string
{
    case PENDING = 'PENDING';
    case DISBURSED = 'DISBURSED';
}
