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
        Schema::create('target_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('energy', 8, 2)->comment('kkal');
            $table->decimal('protein', 8, 2)->comment('gr');
            $table->decimal('fat', 8, 2)->comment('gr');
            $table->decimal('carb', 8, 2)->comment('gr');
            $table->decimal('mineral', 8, 2)->comment('gr')->nullable();
            $table->decimal('vitamin', 8, 2)->comment('gr')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_groups');
    }
};
