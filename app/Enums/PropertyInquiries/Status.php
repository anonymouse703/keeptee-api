<?php

namespace App\Enums\PropertyInquiries;

use App\Enums\Traits\EnumToArray;

enum Status: string
{
    use EnumToArray;

    case Pending = 'pending';
    case Approved = 'approved';
    case Cancelled = 'cancelled';
}
