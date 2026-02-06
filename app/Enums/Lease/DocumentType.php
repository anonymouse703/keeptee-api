<?php

namespace App\Enums\Lease;

use App\Enums\Traits\EnumToArray;

enum DocumentType: string
{
    use EnumToArray;

    case LeaseContract     = 'lease_contract';
    case GovernmentId      = 'government_id';
    case ProofOfIncome     = 'proof_of_income';
    case BankStatement     = 'bank_statement';
    case EmploymentLetter  = 'employment_letter';
    case RentalReference   = 'rental_reference';
    case Other             = 'other';

    public function label(): string
    {
        return match ($this) {
            self::LeaseContract    => 'Lease Contract',
            self::GovernmentId     => 'Government ID',
            self::ProofOfIncome    => 'Proof of Income',
            self::BankStatement    => 'Bank Statement',
            self::EmploymentLetter => 'Employment Letter',
            self::RentalReference  => 'Rental Reference',
            self::Other            => 'Other / Addendum',
        };
    }

    /**
     * Document types required to create a lease
     */
    public static function required(): array
    {
        return [
            self::LeaseContract->value,
            self::GovernmentId->value,
        ];
    }

    /**
     * Options for dropdowns / selects
     */
    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }
}
