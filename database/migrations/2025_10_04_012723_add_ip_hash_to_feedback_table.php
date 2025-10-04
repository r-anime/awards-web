<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the ip_hash column
        Schema::table('feedback', function (Blueprint $table) {
            $table->string('ip_hash', 64)->nullable()->after('ip_address');
            $table->index('ip_hash');
        });

        // Hash existing IP addresses
        $feedbacks = DB::table('feedback')->whereNotNull('ip_address')->get();
        foreach ($feedbacks as $feedback) {
            $ipHash = hash('sha256', $feedback->ip_address . config('app.key'));
            DB::table('feedback')
                ->where('id', $feedback->id)
                ->update(['ip_hash' => $ipHash]);
        }

        // Make ip_hash not nullable and remove ip_address column
        Schema::table('feedback', function (Blueprint $table) {
            $table->string('ip_hash', 64)->nullable(false)->change();
            $table->dropColumn('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore ip_address column and remove ip_hash
        Schema::table('feedback', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('message');
            $table->dropIndex(['ip_hash']);
            $table->dropColumn('ip_hash');
        });
    }
};
