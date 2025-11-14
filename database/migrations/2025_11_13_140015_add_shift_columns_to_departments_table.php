<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('name'); // Jam masuk
            $table->time('end_time')->nullable()->after('start_time'); // Jam pulang
            $table->boolean('is_overnight')->default(false)->after('end_time'); // Melewati tengah malam
            $table->integer('tolerance_late_minutes')->default(15)->after('is_overnight'); // Toleransi telat
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'is_overnight', 'tolerance_late_minutes']);
        });
    }
};
