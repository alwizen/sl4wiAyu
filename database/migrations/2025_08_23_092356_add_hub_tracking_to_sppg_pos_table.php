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
        Schema::table('sppg_purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('hub_intake_id')->nullable()->after('status');
            $table->timestamp('hub_synced_at')->nullable()->after('hub_intake_id');
            $table->text('hub_last_error')->nullable()->after('hub_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sppg_purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['hub_intake_id', 'hub_synced_at', 'hub_last_error']);
        });
    }
};
