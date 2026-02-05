<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lease;
use App\Helpers\RentPaymentHelper;
use App\Models\RentPayment;
use Carbon\Carbon;

class GenerateMonthlyRentPayments extends Command
{
    protected $signature = 'rent:generate-monthly';
    protected $description = 'Generate monthly rent payments for all active leases';

    public function handle(): void
    {
        $today = Carbon::today();

        // Get all active leases
        $leases = Lease::where('status', 'active')->with('rentPayments')->get();

        foreach ($leases as $lease) {
            $this->generateNextPayment($lease, $today);
            $this->info("Processed lease #{$lease->id}");
        }
    }

    protected function generateNextPayment(Lease $lease, Carbon $today): void
    {
        // Determine last payment
        $lastPayment = $lease->rentPayments()->latest('due_date')->first();

        // Calculate next due date
        $nextDueDate = $lastPayment?->due_date?->addMonth() ?? $lease->start_date;

        // Skip if the next payment is in the future
        if ($nextDueDate > $today) return;

        // Check if payment already exists for this month
        $existingPayment = $lease->rentPayments()
            ->whereMonth('due_date', $nextDueDate->month)
            ->whereYear('due_date', $nextDueDate->year)
            ->first();

        if ($existingPayment) return;

        // Create new payment
        $payment = RentPayment::create([
            'lease_id' => $lease->id,
            'base_amount' => $lease->monthly_rent,
            'late_fee_amount' => 0,
            'total_amount' => $lease->monthly_rent,
            'paid_amount' => 0,
            'due_date' => $nextDueDate,
            'status' => 'pending',
        ]);

        // Update total including late fees if past due (rare at generation time)
        $payment->total_amount = RentPaymentHelper::calculateCurrentTotal($lease, $payment);
        $payment->save();
    }
}
