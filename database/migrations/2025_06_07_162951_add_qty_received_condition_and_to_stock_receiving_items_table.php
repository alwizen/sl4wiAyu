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
        Schema::table('stock_receiving_items', function (Blueprint $table) {
            $table->integer('expected_quantity')->nullable()->after('warehouse_item_id');
            $table->integer('received_quantity')->nullable()->change();
            $table->integer('good_quantity')->nullable()->after('received_quantity');
            $table->integer('damaged_quantity')->nullable()->after('good_quantity');
            $table->boolean('is_quantity_matched')->nullable()->after('damaged_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_receiving_items', function (Blueprint $table) {
            $table->dropColumn('expected_quantity');
            $table->dropColumn('received_quantity');
            $table->dropColumn('good_quantity');
            $table->dropColumn('damaged_quantity');
            $table->dropColumn('is_quantity_matched');
        });
    }
};
