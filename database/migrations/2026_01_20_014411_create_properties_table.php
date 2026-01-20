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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('status', ['for_sale', 'for_rent']);
            $table->enum('property_type', [
                'house', 'apartment', 'villa', 'townhouse',
                'office', 'farmhouse', 'cabin', 'chalet'
            ]);

            $table->decimal('price', 12, 2);
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->integer('floor_area')->nullable(); // sqft

            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('USA');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['status', 'city', 'price', 'bedrooms','bathrooms']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
