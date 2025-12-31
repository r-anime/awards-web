<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add nomination voting date options to the options table
        DB::table('options')->insert([
            [
                'option' => 'nomination_voting_start_date',
                'value' => '',
            ],
            [
                'option' => 'nomination_voting_end_date',
                'value' => '',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove nomination voting date options from the options table
        DB::table('options')->whereIn('option', [
            'nomination_voting_start_date',
            'nomination_voting_end_date'
        ])->delete();
    }
};
