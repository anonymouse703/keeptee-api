<?php

namespace App\Enums\RentPayment;

use App\Enums\Traits\EnumToArray;

enum PaymentMethod: string
{
    use EnumToArray;

    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case CreditCard = 'credit_card';
}
