<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /*
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        */

        $this->call([
            OptionSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call([
                DevelopmentResultsSeeder::class,
                DevelopmentAcknowledgementsSeeder::class,
            ]);
        }
    }
}
