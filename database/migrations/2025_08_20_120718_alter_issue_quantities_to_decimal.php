<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_issue_items', function (Blueprint $table) {
            $table->decimal('requested_quantity', 10, 2)->change();
            $table->decimal('issued_quantity', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_issue_items', function (Blueprint $table) {
            $table->integer('requested_quantity')->change();
            $table->integer('issued_quantity')->nullable()->change();
        });
    }
};
