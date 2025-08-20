<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_receiving_items', function (Blueprint $table) {
            $table->decimal('expected_quantity', 10, 2)->nullable()->change();
            $table->decimal('received_quantity', 10, 2)->nullable()->change();
            $table->decimal('good_quantity', 10, 2)->nullable()->change();
            $table->decimal('damaged_quantity', 10, 2)->nullable()->change();
            // is_quantity_matched tetap boolean (tidak perlu diubah)
        });
    }

    public function down(): void
    {
        Schema::table('stock_receiving_items', function (Blueprint $table) {
            $table->integer('expected_quantity')->nullable()->change();
            $table->integer('received_quantity')->nullable()->change();
            $table->integer('good_quantity')->nullable()->change();
            $table->integer('damaged_quantity')->nullable()->change();
        });
    }
};
