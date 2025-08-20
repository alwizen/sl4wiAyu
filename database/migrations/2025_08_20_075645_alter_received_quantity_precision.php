<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_receiving_items', function (Blueprint $table) {
            $table->decimal('received_quantity', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_receiving_items', function (Blueprint $table) {
            $table->integer('received_quantity')->change();
        });
    }
};
