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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique();
            $table->date('delivery_date');
            $table->foreignId('recipient_id')->constrained()->onDelete('cascade');
            $table->integer('qty');
            $table->enum('status', ['dikemas', 'disiapkan', 'dalam_perjalanan', 'terkirim', 'selesai'])->default('dikemas');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('proof_delivery')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->integer('received_qty')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
