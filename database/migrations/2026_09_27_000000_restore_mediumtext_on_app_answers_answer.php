<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restore the original MEDIUMTEXT width on app_answers.answer.
     *
     * 2025_10_04_202423 re-declared the column as text() purely to add
     * nullability, narrowing MySQL from 16MB to 65,535 bytes. Essay questions
     * allow a character_limit of up to 50,000, and multi-byte answers can
     * exceed 65,535 bytes well before that ceiling.
     */
    public function up(): void
    {
        Schema::table('app_answers', function (Blueprint $table) {
            $table->mediumText('answer')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_answers', function (Blueprint $table) {
            $table->text('answer')->nullable()->change();
        });
    }
};
