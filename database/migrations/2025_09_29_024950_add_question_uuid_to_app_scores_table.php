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
            $table->string('question_uuid')->nullable();
            $table->index('question_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_scores', function (Blueprint $table) {
            $table->dropIndex(['question_uuid']);
            $table->dropColumn('question_uuid');
        });
    }
};
