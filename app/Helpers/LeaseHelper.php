<?php

namespace App\Helpers;

use App\Models\Lease;
use App\Helpers\RentPaymentHelper;

class LeaseHelper
{
    /**
     * Calculate current total balance for a lease including late fees
     */
    public static function calculateCurrentBalance(Lease $lease): float
    {
        $total = 0;

        $lease->rentPayments()->get()->each(function ($payment) use (&$total, $lease) {
            $total += RentPaymentHelper::calculateCurrentTotal($lease, $payment);
        });

        return round($total, 2);
    }

    /**
     * Get a detailed list of payments with late fees and total due
     */
    public static function getPaymentsSummary(Lease $lease): array
    {
        return $lease->rentPayments->map(function ($payment) use ($lease) {
            $remaining = RentPaymentHelper::getRemainingAmount($payment);
            $lateFee = RentPaymentHelper::calculateLateFee($lease, $payment);
            return [
                'id' => $payment->id,
                'due_date' => $payment->due_date->toDateString(),
                'base_amount' => $payment->base_amount,
                'paid_amount' => $payment->paid_amount,
                'remaining_amount' => $remaining,
                'late_fee' => $lateFee,
                'total_due' => $remaining + $lateFee,
                'status' => $payment->status,
            ];
        })->toArray();
    }
}
