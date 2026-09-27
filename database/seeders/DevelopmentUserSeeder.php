<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DevelopmentUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('The development user can only be seeded in local or testing environments.');
        }

        $user = User::query()->firstOrNew([
            'uuid' => config('auth.local_login.user_uuid'),
        ]);

        $user->fill([
            'name' => 'Local Administrator',
            'email' => null,
            'reddit_user' => null,
            'role' => 2,
            'flags' => 0,
        ]);

        if (! $user->exists) {
            $user->password = Hash::make(Str::random(64));
        }

        $user->save();
    }
}
