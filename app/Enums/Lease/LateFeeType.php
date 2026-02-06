<?php

namespace App\Enums\Lease;

use App\Enums\Traits\EnumToArray;

enum LateFeeType: string
{
    use EnumToArray;

    case Fixed = 'fixed';
    case Daily = 'daily';
    case Percentage = 'percentage';
}
