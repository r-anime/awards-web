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
        Schema::create('watch_stats_calculated', function (Blueprint $table) {
            $table->foreignId('category_id');
            $table->foreignId('anime_id');
            $table->integer('votes');
            $table->double('watched');
            $table->double('supported');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_stats_calculated');
    }
};
