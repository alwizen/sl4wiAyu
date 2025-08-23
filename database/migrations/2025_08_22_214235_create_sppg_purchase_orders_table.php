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
        Schema::create('sppg_purchase_orders', function (Blueprint $t) {
            $t->id();
            $t->string('po_number', 64)->unique();
            $t->date('requested_at');
            $t->time('delivery_time')->nullable();
            $t->enum('status', ['Draft', 'Submitted'])->default('Draft')->index();
            $t->text('notes')->nullable();
            $t->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppg_purchase_orders');
    }
};
