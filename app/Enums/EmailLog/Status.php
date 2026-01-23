<?php

namespace App\Enums\EmailLog;

use App\Enums\Traits\EnumToArray;

enum Status: string
{
    use EnumToArray;

    case Sent = 'sent';
    case Failed = 'failed';
}
