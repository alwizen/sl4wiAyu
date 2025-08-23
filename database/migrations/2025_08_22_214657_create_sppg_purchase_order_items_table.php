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
        Schema::create('sppg_purchase_order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sppg_purchase_order_id')
                ->constrained('sppg_purchase_orders')
                ->cascadeOnDelete();

            $t->foreignId('warehouse_item_id')->nullable()
                ->constrained('warehouse_items')->nullOnDelete();

            $t->string('item_name', 191)->nullable();
            $t->decimal('qty', 12, 3);
            $t->string('unit', 20)->nullable();
            $t->string('note', 255)->nullable();
            $t->timestamps();

            $t->index('sppg_purchase_order_id');
            $t->index('warehouse_item_id');
            $t->index('item_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppg_purchase_order_items');
    }
};
