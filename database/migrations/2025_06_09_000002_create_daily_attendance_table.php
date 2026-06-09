<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->date('delivery_date');
            $table->enum('status', ['delivered', 'skipped']);
            $table->decimal('cow_milk_delivered', 8, 2)->default(0);
            $table->decimal('buffalo_milk_delivered', 8, 2)->default(0);
            $table->decimal('cow_price_per_liter', 8, 2)->default(0);
            $table->decimal('buffalo_price_per_liter', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['house_id', 'delivery_date']);
            $table->index('delivery_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_attendance');
    }
};
