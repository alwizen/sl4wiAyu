<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('warehouse_items', function (Blueprint $table) {
            // Naikkan presisi ke 3 desimal (ubah sesuai kebutuhan)
            $table->decimal('stock', 18, 3)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->decimal('stock', 10, 2)->default(0)->change();
        });
    }
};
