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
        Schema::table('users', function (Blueprint $table) {
            $table->string('reddit_user', length:32)->after('remember_token')->nullable();
            $table->integer('role')->after('reddit_user');
            $table->integer('flags')->after('role');
            $table->text('avatar')->after('flags')->nullable();
            $table->text('about')->after('avatar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reddit_user', 'role', 'flags']);
        });
    }
};
