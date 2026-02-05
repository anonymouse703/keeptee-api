<?php

namespace App\Jobs\RentPayment;

use App\Models\Lease;
use App\Models\RentPayment;
use App\Helpers\RentPaymentHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateRentPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Lease $lease;

    public function __construct(Lease $lease)
    {
        $this->lease = $lease;
    }

    public function handle(): void
    {
        // Determine next due date (e.g., monthly schedule)
        $lastPayment = $this->lease->rentPayments()->latest('due_date')->first();
        $nextDueDate = $lastPayment?->due_date?->addMonth() ?? $this->lease->start_date;

        // Create new rent payment
        $payment = new RentPayment([
            'lease_id' => $this->lease->id,
            'base_amount' => $this->lease->monthly_rent,
            'late_fee_amount' => 0,  // initially 0, will calculate dynamically later
            'total_amount' => $this->lease->monthly_rent, // total will be dynamically updated later
            'paid_amount' => 0,
            'due_date' => $nextDueDate,
            'status' => 'pending',
            'payment_method' => null,
            'notes' => null,
        ]);

        $payment->save();

        // Optional: calculate dynamic total if generating after due date
        $payment->total_amount = RentPaymentHelper::calculateCurrentTotal($this->lease, $payment);
        $payment->save();
    }
}
