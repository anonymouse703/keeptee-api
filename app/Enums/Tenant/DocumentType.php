<?php

namespace App\Enums\Tenant;

use App\Enums\Traits\EnumToArray;

enum DocumentType: string
{
    use EnumToArray;
    
    case Lease = 'lease_contract';
    case GovernmentId = 'government_id';
    case ProofOfIncome = 'proof_of_income';
    case RentalReference = 'rental_reference';
    case BankStatement = 'bank_statement';
    case EmploymentLetter = 'employment_letter';
    case Other = 'other';

    public function label(): string
    {
        return match($this) {
            self::Lease => 'Lease Contract',
            self::GovernmentId => 'Government ID',
            self::ProofOfIncome => 'Proof of Income',
            self::RentalReference => 'Rental Reference',
            self::BankStatement => 'Bank Statement',
            self::EmploymentLetter => 'Employment Letter',
            self::Other => 'Other',
        };
    }

    public static function options(): array
    {
        return array_column(self::cases(), 'value');
    }
}