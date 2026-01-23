<?php

namespace App\Enums\RentPayment;

use App\Enums\Traits\EnumToArray;

enum Status: string
{
    use EnumToArray;

    case Pending = 'pending';
    case Pain = 'paid';
    case Overdue = 'overdue';
}
