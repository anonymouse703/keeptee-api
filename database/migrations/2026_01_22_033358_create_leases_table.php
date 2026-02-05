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
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->decimal('monthly_rent', 12, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->enum('status', ['active', 'ended', 'terminated']);
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();

            // ⚡ Late-fee rules
            $table->tinyInteger('rent_due_day')->default(1); // 1–31
            $table->tinyInteger('grace_period_days')->default(0);
            $table->enum('late_fee_type', ['fixed','daily','percentage'])->nullable();
            $table->decimal('late_fee_value', 8, 4)->nullable(); // 1% = 0.01
            $table->decimal('late_fee_cap', 12, 2)->nullable();

            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
