<?php

namespace App\Domain;

enum DisbursementFrequency: string
{
    case DAILY = 'DAILY';
    case WEEKLY = 'WEEKLY';
}
