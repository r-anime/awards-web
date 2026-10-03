<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bit 1 of users.flags (User::FLAG_AGE_RESTRICTED), inlined so this migration
     * doesn't depend on the model.
     */
    private const FLAG_AGE_RESTRICTED = 1;

    /**
     * Every role -1 user so far was restricted by the account age check, which now
     * lives in flags; their next login re-checks them. Query builder updates skip
     * the User model's audit webhook.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', -1)
            ->update([
                'role' => 0,
                'flags' => DB::raw('flags | '.self::FLAG_AGE_RESTRICTED),
            ]);
    }

    /**
     * Only role 0 users go back to -1, so staff the login check has flagged keep their role.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('role', 0)
            ->whereRaw('(flags & ?) != 0', [self::FLAG_AGE_RESTRICTED])
            ->update([
                'role' => -1,
                'flags' => DB::raw('flags & ~'.self::FLAG_AGE_RESTRICTED),
            ]);
    }
};
