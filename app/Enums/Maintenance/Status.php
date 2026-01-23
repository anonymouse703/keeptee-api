<?php

namespace App\Enums\Maintenance;

use App\Enums\Traits\EnumToArray;

enum Status: string
{
    use EnumToArray;

    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
