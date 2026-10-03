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
        DB::table('options')->insert([
            [
                'option' => 'show_applicant_names_to_hosts',
                'value' => 'false',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('options')->where('option', 'show_applicant_names_to_hosts')->delete();
    }
};
