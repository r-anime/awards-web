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
        // Add default UUID value using raw SQL since Laravel doesn't support UUID defaults directly
        DB::statement("ALTER TABLE users MODIFY COLUMN uuid CHAR(36) NOT NULL DEFAULT (UUID())");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the default UUID value
        DB::statement("ALTER TABLE users MODIFY COLUMN uuid CHAR(36) NOT NULL");
    }
};
