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
        // Add webhook options to the options table
        DB::table('options')->insert([
            [
                'option' => 'feedback_channel_webhook',
                'value' => '',
            ],
            [
                'option' => 'audit_channel_webhook',
                'value' => '',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove webhook options from the options table
        DB::table('options')->whereIn('option', [
            'feedback_channel_webhook',
            'audit_channel_webhook'
        ])->delete();
    }
};
