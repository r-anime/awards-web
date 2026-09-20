<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Generate a random password hash for existing users who don't have a password
        $usersWithoutPassword = DB::table('users')
            ->whereNull('password')
            ->orWhere('password', '')
            ->get();

        foreach ($usersWithoutPassword as $user) {
            $randomPassword = Hash::make(Str::random(32));
            DB::table('users')
                ->where('id', $user->id)
                ->update(['password' => $randomPassword]);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                CREATE TRIGGER set_default_password
                BEFORE INSERT ON users
                FOR EACH ROW
                BEGIN
                    IF NEW.password IS NULL OR NEW.password = '' THEN
                        SET NEW.password = SHA2(CONCAT(NEW.id, UNIX_TIMESTAMP(), RAND()), 256);
                    END IF;
                END
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS set_default_password');
        }
    }
};
