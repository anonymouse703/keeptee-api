<?php

namespace App\Enums\Property;

use App\Enums\Traits\EnumToArray;

enum Status: string
{
    use EnumToArray;

    case ForSale = 'for_sale';
    case ForRent = 'for_rent';
}
