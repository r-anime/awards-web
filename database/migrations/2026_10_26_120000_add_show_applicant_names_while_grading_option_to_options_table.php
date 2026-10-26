<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip if settings were saved before this ran; options.option is unique since the previous migration.
        // A plain check rather than insertOrIgnore, which on MySQL (INSERT IGNORE) also swallows other errors
        if (DB::table('options')->where('option', 'show_applicant_names_while_grading')->exists()) {
            return;
        }

        DB::table('options')->insert([
            [
                'option' => 'show_applicant_names_while_grading',
                'value' => 'false',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('options')->where('option', 'show_applicant_names_while_grading')->delete();
    }
};
