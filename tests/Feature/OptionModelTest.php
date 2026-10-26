<?php

namespace Tests\Feature;

use App\Models\Option;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OptionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_many_returns_saved_values_and_falls_back_for_unsaved_ones(): void
    {
        Option::set('site_name', 'Saved Name');

        $this->assertSame(
            ['site_name' => 'Saved Name', 'never_saved_option' => 'fallback'],
            Option::getMany(['site_name' => 'r/anime Awards', 'never_saved_option' => 'fallback']),
        );
    }

    public function test_set_many_inserts_new_options_and_updates_existing_ones(): void
    {
        Option::set('site_name', 'Old Name');

        Option::setMany(['site_name' => 'New Name', 'brand_new_option' => 'value']);

        $this->assertSame('New Name', Option::get('site_name'));
        $this->assertSame('value', Option::get('brand_new_option'));
        $this->assertSame(1, Option::where('option', 'site_name')->count());
    }

    public function test_set_many_fires_model_events_for_each_option(): void
    {
        $saved = [];
        Option::saved(function (Option $option) use (&$saved) {
            $saved[] = $option->option;
        });

        Option::setMany(['site_name' => 'New Name', 'brand_new_option' => 'value']);

        $this->assertSame(['site_name', 'brand_new_option'], $saved);
    }

    public function test_set_many_saves_nothing_if_one_option_fails(): void
    {
        Option::set('site_name', 'Old Name');

        try {
            // value is NOT NULL, so the second option fails after the first was written
            Option::setMany(['site_name' => 'New Name', 'max_file_size' => null]);
            $this->fail('Expected the null value to be rejected');
        } catch (QueryException) {
            // expected
        }

        $this->assertSame('Old Name', Option::get('site_name'));
        $this->assertFalse(Option::where('option', 'max_file_size')->exists());
    }

    public function test_set_many_with_nothing_to_save_runs_no_query(): void
    {
        DB::enableQueryLog();
        Option::setMany([]);

        $this->assertSame([], DB::getQueryLog());
    }
}
