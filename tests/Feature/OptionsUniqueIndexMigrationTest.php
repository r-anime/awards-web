<?php

namespace Tests\Feature;

use App\Models\Option;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OptionsUniqueIndexMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_keeps_the_row_options_already_used_and_makes_option_names_unique(): void
    {
        $migration = require database_path('migrations/2026_10_26_110000_add_unique_index_to_options_option_column.php');

        // Back to the old schema, with the kind of duplicates it allowed
        $migration->down();
        DB::table('options')->delete();
        DB::table('options')->insert([
            ['option' => 'site_name', 'value' => 'First'],
            ['option' => 'site_name', 'value' => 'Second'],
            ['option' => 'max_file_size', 'value' => '10MB'],
            ['option' => 'site_name', 'value' => 'Third'],
        ]);
        $this->assertSame('First', Option::get('site_name'));

        $migration->up();

        $this->assertSame(
            ['site_name' => 'First', 'max_file_size' => '10MB'],
            DB::table('options')->orderBy('id')->pluck('value', 'option')->all(),
        );

        $this->expectException(QueryException::class);
        DB::table('options')->insert(['option' => 'site_name', 'value' => 'Duplicate']);
    }
}
