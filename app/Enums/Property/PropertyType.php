<?php

namespace App\Enums\Property;

use App\Enums\Traits\EnumToArray;

enum PropertyType: string
{
    use EnumToArray;

    case House = 'house';
    case Apartment = 'apartment';
    case Villa = 'villa';
    case Townhouse = 'villa';
    case Ofice = 'office';
    case Farmhouse = 'farmhouse';
    case Cabin = 'cabin';
    case Chalet = 'chalet';
}
