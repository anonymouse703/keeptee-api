<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('rent_payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
        $table->decimal('base_amount', 12, 2);       // original rent
        $table->decimal('late_fee_amount', 12, 2);   // calculated late fees
        $table->decimal('total_amount', 12, 2);      // base + late fees
        $table->decimal('paid_amount', 12, 2)->default(0); // partial payments
        $table->date('due_date');
        $table->date('paid_at')->nullable();

        $table->enum('status', ['pending','paid','overdue','partial','failed'])->default('pending');
        $table->enum('payment_method', ['cash','bank_transfer','credit_card'])->nullable();
        $table->string('reference_id')->unique()->nullable();
        $table->text('notes')->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_payments');
    }
};
