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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->foreignId('supplier_id')->constrained();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['Pending', 'Ordered', 'Approved', 'Rejected',]); 
            $table->text('note')->nullable();
            $table->enum('payment_status', ['Paid', 'Unpaid', 'Partially Paid'])->default('Unpaid');
            $table->date('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
