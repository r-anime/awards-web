<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Remove orphaned mappings first to avoid FK violations
        DB::table('socialite_users as su')
            ->leftJoin('users as u', 'u.id', '=', 'su.user_id')
            ->whereNull('u.id')
            ->delete();

        Schema::table('socialite_users', function (Blueprint $table) {
            // Ensure an index exists on user_id (safe if already present)
            $table->index('user_id', 'socialite_users_user_id_index');

            // Add the foreign key with cascade delete if it doesn't exist
            // Some databases require dropping any existing FK first – we assume none existed.
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('socialite_users', function (Blueprint $table) {
            // Drop FK and index if rolling back
            try {
                $table->dropForeign(['user_id']);
            } catch (\Throwable $e) {
                // ignore if it didn't exist
            }
            try {
                $table->dropIndex('socialite_users_user_id_index');
            } catch (\Throwable $e) {
                // ignore if it didn't exist
            }
        });
    }
};


