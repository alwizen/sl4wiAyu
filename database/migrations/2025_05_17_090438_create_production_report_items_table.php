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
        Schema::create('production_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_report_id')->constrained()->onDelete('cascade');
            $table->foreignId('daily_menu_item_id')->constrained()->onDelete('cascade');
            $table->integer('target_qty');
            $table->integer('actual_qty');
            $table->enum('status', ['tercukupi', 'kurang', 'lebih']);
            $table->integer('diference_qty')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_report_items');
    }
};
