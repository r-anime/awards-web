<?php

namespace Tests\Feature\OneOff;

use App\Models\AppAnswer;
use App\Models\Application;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackfillOctober2026RankingsTest extends TestCase
{
    use RefreshDatabase;

    private array $mainIds;

    private Category $op;

    protected function setUp(): void
    {
        parent::setUp();

        // Answers are saved during the October 2026 window unless a test moves the clock
        $this->travelTo('2026-10-10 12:00:00');

        Application::create([
            'year' => 2026,
            'start_time' => '2026-10-03 18:00:00',
            'end_time' => '2026-10-18 23:59:00',
            'form' => [
                ['id' => 'pref', 'type' => 'preference', 'question' => 'Preferences'],
            ],
        ]);

        // Created in the same order as 2026's, so id order (the order the form showed) is MV first, AOTY last
        $this->mainIds = collect(['Music Video of the Year', 'Short Film of the Year', 'Movie of the Year', 'Anime of the Year'])
            ->map(fn ($name) => $this->category($name, 'main')->id)
            ->all();
        $this->op = $this->category('OP', 'production');
    }

    public function test_untouched_rankings_get_the_order_the_form_showed_in_the_forms_format(): void
    {
        // The shape real untouched answers have: Laravel turns the form's empty fields into null
        $answer = $this->preferenceAnswer(['non_main' => [(string) $this->op->id], 'main_order' => null, 'no_main_order' => null]);

        $this->artisan('one-off:backfill-october-2026-rankings', ['--force' => true])->assertSuccessful();

        // Exactly what the form's JSON.stringify would have saved: string ids, ranks from 1, no spaces
        [$mv, $short, $movie, $aoty] = $this->mainIds;
        $shownOrder = "[{\"category_id\":\"{$mv}\",\"order\":1},{\"category_id\":\"{$short}\",\"order\":2},"
            ."{\"category_id\":\"{$movie}\",\"order\":3},{\"category_id\":\"{$aoty}\",\"order\":4}]";

        $this->assertSame(
            json_encode(['non_main' => [(string) $this->op->id], 'main_order' => $shownOrder, 'no_main_order' => '[]']),
            $answer->fresh()->answer,
        );
    }

    public function test_untouched_rankings_stored_as_null_or_empty_strings_are_both_found(): void
    {
        $asNull = $this->preferenceAnswer(['non_main' => [], 'main_order' => null, 'no_main_order' => null]);
        $asEmptyStrings = $this->preferenceAnswer(['non_main' => [], 'main_order' => '', 'no_main_order' => '']);

        $this->artisan('one-off:backfill-october-2026-rankings', ['--force' => true])
            ->expectsOutputToContain('Untouched rankings saved in October 2026: 2')
            ->assertSuccessful();

        foreach ([$asNull, $asEmptyStrings] as $answer) {
            $this->assertSame('[]', json_decode($answer->fresh()->answer, true)['no_main_order']);
        }
    }

    public function test_backfill_keeps_the_applicants_own_updated_at(): void
    {
        $answer = $this->preferenceAnswer(['non_main' => [], 'main_order' => '', 'no_main_order' => '']);
        DB::table('app_answers')->where('id', $answer->id)->update(['updated_at' => '2026-10-05 10:00:00']);

        $this->artisan('one-off:backfill-october-2026-rankings', ['--force' => true])->assertSuccessful();

        $this->assertSame('2026-10-05 10:00:00', (string) $answer->fresh()->updated_at);
    }

    public function test_only_answers_last_saved_in_october_2026_are_changed(): void
    {
        $september = $this->preferenceAnswerSavedAt('2026-09-30 23:59:59');
        $octoberStart = $this->preferenceAnswerSavedAt('2026-10-01 00:00:00');
        $octoberEnd = $this->preferenceAnswerSavedAt('2026-10-31 23:59:59');
        $november = $this->preferenceAnswerSavedAt('2026-11-01 00:00:00');
        $untouched = json_encode(['non_main' => [], 'main_order' => '', 'no_main_order' => '']);

        $this->artisan('one-off:backfill-october-2026-rankings', ['--force' => true])
            ->expectsOutputToContain('Untouched rankings saved in October 2026: 2')
            ->assertSuccessful();

        $this->assertSame($untouched, $september->fresh()->answer);
        $this->assertNotSame($untouched, $octoberStart->fresh()->answer);
        $this->assertNotSame($untouched, $octoberEnd->fresh()->answer);
        $this->assertSame($untouched, $november->fresh()->answer);
    }

    public function test_rankings_the_applicant_set_are_left_alone(): void
    {
        [$mv, $short, $movie, $aoty] = $this->mainIds;
        $ranked = $this->preferenceAnswer([
            'non_main' => [],
            'main_order' => json_encode([['category_id' => (string) $aoty, 'order' => 1], ['category_id' => (string) $movie, 'order' => 2]]),
            'no_main_order' => json_encode([['category_id' => (string) $mv, 'order' => 1], ['category_id' => (string) $short, 'order' => 2]]),
        ]);
        $allExcluded = $this->preferenceAnswer([
            'non_main' => [],
            'main_order' => '[]',
            'no_main_order' => json_encode(collect($this->mainIds)->map(fn ($id, $index) => ['category_id' => (string) $id, 'order' => $index + 1])->all()),
        ]);
        $before = [$ranked->answer, $allExcluded->answer];

        $this->artisan('one-off:backfill-october-2026-rankings', ['--force' => true])
            ->expectsOutputToContain('Untouched rankings saved in October 2026: 0')
            ->assertSuccessful();

        $this->assertSame($before, [$ranked->fresh()->answer, $allExcluded->fresh()->answer]);
    }

    public function test_other_questions_are_left_alone(): void
    {
        $otherYear = AppAnswer::create([
            'applicant_id' => User::factory()->create()->id,
            'question_id' => 'last-years-pref',
            'answer' => json_encode(['non_main' => [], 'main_order' => '', 'no_main_order' => '']),
        ]);
        $before = $otherYear->answer;

        $this->artisan('one-off:backfill-october-2026-rankings', ['--force' => true])->assertSuccessful();

        $this->assertSame($before, $otherYear->fresh()->answer);
    }

    public function test_without_force_it_only_reports(): void
    {
        $answer = $this->preferenceAnswer(['non_main' => [], 'main_order' => '', 'no_main_order' => '']);
        $before = $answer->answer;

        $this->artisan('one-off:backfill-october-2026-rankings')
            ->expectsOutputToContain('Untouched rankings saved in October 2026: 1')
            ->expectsOutputToContain('Applicant ids: '.$answer->applicant_id)
            ->expectsOutputToContain('Dry run')
            ->assertSuccessful();

        $this->assertSame($before, $answer->fresh()->answer);
    }

    public function test_running_it_again_changes_nothing(): void
    {
        $answer = $this->preferenceAnswer(['non_main' => [], 'main_order' => '', 'no_main_order' => '']);

        $this->artisan('one-off:backfill-october-2026-rankings', ['--force' => true])->assertSuccessful();
        $afterFirstRun = $answer->fresh()->answer;

        $this->artisan('one-off:backfill-october-2026-rankings', ['--force' => true])
            ->expectsOutputToContain('Untouched rankings saved in October 2026: 0')
            ->assertSuccessful();

        $this->assertSame($afterFirstRun, $answer->fresh()->answer);
    }

    public function test_fails_without_a_2026_application(): void
    {
        Application::query()->delete();

        $this->artisan('one-off:backfill-october-2026-rankings')->assertFailed();
    }

    private function preferenceAnswer(array $preferences): AppAnswer
    {
        return AppAnswer::create([
            'applicant_id' => User::factory()->create()->id,
            'question_id' => 'pref',
            'answer' => json_encode($preferences),
        ]);
    }

    private function preferenceAnswerSavedAt(string $savedAt): AppAnswer
    {
        $answer = $this->preferenceAnswer(['non_main' => [], 'main_order' => '', 'no_main_order' => '']);
        DB::table('app_answers')->where('id', $answer->id)->update(['created_at' => $savedAt, 'updated_at' => $savedAt]);

        return $answer;
    }

    private function category(string $name, string $type): Category
    {
        return Category::forceCreate(['year' => 2026, 'name' => $name, 'type' => $type, 'entry_type' => 'anime']);
    }
}
