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
        Schema::table('app_scores', function (Blueprint $table) {
            $table->string('question_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_scores', function (Blueprint $table) {
            $table->unsignedBigInteger('question_id')->nullable()->change();
        });
    }
};
