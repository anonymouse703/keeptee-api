<?php

namespace App\Enums\Lease;

use App\Enums\Traits\EnumToArray;

enum Status: string
{
    use EnumToArray;

    case Active = 'active';
    case Ended = 'ended';
    case Terminated = 'terminated';
}
