<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Option;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add Account Age option
        Option::updateOrCreate(
            ['option' => 'account_age_requirement'],
            ['value' => '30'] // Default to 30 days
        );

        // Add other common options that might be useful
        Option::updateOrCreate(
            ['option' => 'site_name'],
            ['value' => 'r/anime Awards']
        );

        Option::updateOrCreate(
            ['option' => 'maintenance_mode'],
            ['value' => 'false']
        );

        Option::updateOrCreate(
            ['option' => 'max_file_size'],
            ['value' => '10MB']
        );
    }
}
