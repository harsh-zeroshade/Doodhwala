<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->date('order_date');
            $table->enum('type', ['hold', 'extra']);
            $table->decimal('extra_cow_milk', 8, 2)->default(0);
            $table->decimal('extra_buffalo_milk', 8, 2)->default(0);
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->index(['house_id', 'order_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_orders');
    }
};
