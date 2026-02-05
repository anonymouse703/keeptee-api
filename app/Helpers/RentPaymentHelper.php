<?php

namespace App\Helpers;

use App\Models\Lease;
use App\Models\RentPayment;
use Carbon\Carbon;

class RentPaymentHelper
{
    public static function getRemainingAmount(RentPayment $payment): float
    {
        return max(0, $payment->total_amount - $payment->paid_amount);
    }

    public static function applyPayment(RentPayment $payment, float $amount, ?string $method = null): void
    {
        $payment->paid_amount += $amount;
        $payment->payment_method = $method ?? $payment->payment_method;

        $remaining = self::getRemainingAmount($payment);

        if ($remaining <= 0) {
            $payment->status = 'paid';
            $payment->paid_at = now();
        } elseif ($payment->paid_amount > 0) {
            $payment->status = 'partial';
        }

        $payment->save();
    }

    public static function calculateLateFee(Lease $lease, RentPayment $payment): float
    {
        $remaining = self::getRemainingAmount($payment);
        if ($remaining <= 0) return 0;

        $dueDate = Carbon::parse($payment->due_date);
        if (now()->lte($dueDate)) return 0;

        $daysLate = $dueDate->diffInDays(now());
        $chargeableDays = max(0, $daysLate - ($lease->grace_period_days ?? 0));

        if ($chargeableDays === 0 || !$lease->late_fee_type) return 0;

        $fee = match ($lease->late_fee_type) {
            'fixed'      => $lease->late_fee_value,
            'percentage' => $remaining * $lease->late_fee_value,
            'daily'      => $remaining * $lease->late_fee_value * $chargeableDays,
            default      => 0,
        };

        if ($lease->late_fee_cap !== null) {
            $fee = min($fee, $lease->late_fee_cap);
        }

        return round($fee, 2);
    }

    public static function calculateCurrentTotal(Lease $lease, RentPayment $payment): float
    {
        return self::getRemainingAmount($payment) + self::calculateLateFee($lease, $payment);
    }
}
