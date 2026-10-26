<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\GradingPage;
use App\Filament\Admin\Pages\JurorAllocations;
use App\Models\AppAnswer;
use App\Models\Application;
use App\Models\AppScore;
use App\Models\Category;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class JurorAllocationsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $host;

    private User $strong;

    private User $borderline;

    private User $belowCutoff;

    private Category $aoty;

    private Category $moty;

    private Category $op;

    private Category $drama;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        session(['selected-year-filter' => app('current-year')]);

        Application::create([
            'year' => app('current-year'),
            'start_time' => now()->subWeek(),
            'end_time' => now()->addWeek(),
            'form' => [
                ['id' => 'essay-1', 'type' => 'essay', 'question' => 'First essay'],
                ['id' => 'essay-2', 'type' => 'essay', 'question' => 'Second essay'],
                ['id' => 'pref', 'type' => 'preference', 'question' => 'Preferences'],
                ['id' => 'mc-1', 'type' => 'multiple_choice', 'question' => 'First choice', 'options' => [
                    ['id' => 'mc-1-yes', 'option' => 'Yes'],
                    ['id' => 'mc-1-no', 'option' => 'No'],
                ]],
                // "No" listed first and a question without a "Yes" option, so the Yes lookup can't
                // rely on option order or on every question having one
                ['id' => 'mc-2', 'type' => 'multiple_choice', 'question' => 'Second choice', 'options' => [
                    ['id' => 'mc-2-no', 'option' => 'No'],
                    ['id' => 'mc-2-yes', 'option' => ' YES '],
                ]],
                ['id' => 'mc-3', 'type' => 'multiple_choice', 'question' => 'Third choice', 'options' => [
                    ['id' => 'mc-3-often', 'option' => 'Often'],
                    ['id' => 'mc-3-never', 'option' => 'Never'],
                ]],
            ],
        ]);

        // Main categories are ranked, OP/ED are ticked, and other non-main categories (like genres)
        // are never allocated. Names avoid "op"/"ed" so only the OP category matches the OP/ED rule
        $this->aoty = $this->category('Anime of the Year', 'main');
        $this->moty = $this->category('Movie of the Year', 'main');
        $this->op = $this->category('OP', 'production');
        $this->drama = $this->category('Drama', 'genre');

        $this->admin = User::factory()->create(['role' => 4]);
        $this->host = User::factory()->create(['role' => 2]);

        // Total 4: clears the main cutoff (2.5) for AOTY and MOTY, and Q2 clears it for OP
        $this->strong = User::factory()->create(['name' => 'Strong Sam']);
        $this->applicant($this->strong, [4, 4], [$this->aoty, $this->moty], [$this->op, $this->drama]);

        // Total 1.5: on the page (base cutoff 1.5) but below the main cutoff, so unallocated
        $this->borderline = User::factory()->create(['name' => 'Borderline Bea']);
        $this->applicant($this->borderline, [2, 1], [$this->aoty], [$this->op]);

        // Total 1: below the base cutoff, so not on the page at all
        $this->belowCutoff = User::factory()->create(['name' => 'Below Bo']);
        $this->applicant($this->belowCutoff, [1, 1], [$this->aoty], []);
    }

    public function test_table_shows_scores_preferences_and_allocations_per_applicant(): void
    {
        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->assertCanSeeTableRecords([$this->strong, $this->borderline])
            ->assertCanNotSeeTableRecords([$this->belowCutoff])
            ->assertTableColumnStateSet('total_average_score', 4, $this->strong)
            ->assertTableColumnStateSet('score_Q1', 4, $this->strong)
            ->assertTableColumnStateSet('score_Q2', 4, $this->strong)
            ->assertTableColumnStateSet('allocated_to', ['Anime of the Year', 'Movie of the Year', 'OP'], $this->strong)
            ->assertTableColumnStateSet('main_preferences', ['1. Anime of the Year', '2. Movie of the Year'], $this->strong)
            ->assertTableColumnStateSet('non_main_preferences', ['OP', 'Drama'], $this->strong)
            ->assertTableColumnStateSet('score_Q1', 2, $this->borderline)
            ->assertTableColumnStateSet('score_Q2', 1, $this->borderline)
            ->assertTableColumnStateSet('allocated_to', null, $this->borderline);
    }

    public function test_allocations_follow_the_cutoffs(): void
    {
        $page = Livewire::actingAs($this->admin)->test(JurorAllocations::class)->instance();

        $allocations = $page->currentAllocations()['categories'];

        $this->assertSame(['Strong Sam'], array_column($allocations[$this->aoty->id]['allocations'], 'name'));
        $this->assertSame(['Strong Sam'], array_column($allocations[$this->moty->id]['allocations'], 'name'));
        $this->assertSame(['Strong Sam'], array_column($allocations[$this->op->id]['allocations'], 'name'));
        $this->assertArrayNotHasKey($this->drama->id, $allocations);
    }

    public function test_allocated_categories_are_listed_in_the_admin_set_order(): void
    {
        // Admin order differs from creation (id) order: OP first, then AOTY and MOTY tied on 2
        $this->op->update(['order' => 1]);
        $this->moty->update(['order' => 2]);
        $this->aoty->update(['order' => 2]);

        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->assertTableColumnStateSet('allocated_to', ['OP', 'Anime of the Year', 'Movie of the Year'], $this->strong);
    }

    public function test_a_click_loads_the_application_categories_and_applicants_only_once(): void
    {
        $page = Livewire::actingAs($this->admin)->test(JurorAllocations::class);

        $cacheReads = 0;
        Event::listen(CacheHit::class, function (CacheHit $event) use (&$cacheReads) {
            if (str_starts_with($event->key, 'juror_allocations_')) {
                $cacheReads++;
            }
        });
        DB::enableQueryLog();

        $page->sortTable('total_average_score');

        // Matched without quote characters, which differ between databases
        $queries = collect(DB::getQueryLog())->pluck('query');
        $this->assertSame(1, $queries->filter(fn ($sql) => preg_match('/from\W+applications\W/', $sql))->count(), 'application queries');
        $this->assertSame(1, $queries->filter(fn ($sql) => preg_match('/from\W+categories\W/', $sql) && str_contains($sql, 'type'))->count(), 'main category queries');
        $this->assertSame(1, $cacheReads, 'reads of the cached applicant list');
    }

    public function test_results_remembered_for_a_request_follow_cutoff_changes_within_it(): void
    {
        // The same page object, as within one request: a remembered result must not outlive its cutoff
        $page = Livewire::actingAs($this->admin)->test(JurorAllocations::class)->instance();
        $this->assertTrue($this->listsApplicant($page, $this->borderline));
        $this->assertSame(['Strong Sam'], array_column($page->currentAllocations()['categories'][$this->aoty->id]['allocations'], 'name'));

        $page->baseCutoff = 2;
        $page->mainCategoryCutoff = 4.5;

        $this->assertFalse($this->listsApplicant($page, $this->borderline), 'eligible list after raising the base cutoff');
        $this->assertSame([], $page->currentAllocations()['categories'][$this->aoty->id]['allocations'], 'allocations after raising the main cutoff');
    }

    public function test_raising_the_main_cutoff_updates_the_allocated_column(): void
    {
        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->assertTableColumnStateSet('allocated_to', ['Anime of the Year', 'Movie of the Year', 'OP'], $this->strong)
            ->set('mainCategoryCutoff', 4.5)
            ->assertTableColumnStateSet('allocated_to', null, $this->strong);
    }

    public function test_table_sorts_by_total_and_question_scores(): void
    {
        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->sortTable('total_average_score')
            ->assertCanSeeTableRecords([$this->borderline, $this->strong], inOrder: true)
            ->sortTable('total_average_score', 'desc')
            ->assertCanSeeTableRecords([$this->strong, $this->borderline], inOrder: true)
            ->sortTable('score_Q2')
            ->assertCanSeeTableRecords([$this->borderline, $this->strong], inOrder: true);
    }

    public function test_table_searches_by_name(): void
    {
        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->searchTable('bea')
            ->assertCanSeeTableRecords([$this->borderline])
            ->assertCanNotSeeTableRecords([$this->strong]);
    }

    public function test_table_filters_by_allocated_category(): void
    {
        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->filterTable('allocated_to', $this->aoty->id)
            ->assertCanSeeTableRecords([$this->strong])
            ->assertCanNotSeeTableRecords([$this->borderline])
            ->filterTable('allocated_to', 'unallocated')
            ->assertCanSeeTableRecords([$this->borderline])
            ->assertCanNotSeeTableRecords([$this->strong]);
    }

    public function test_table_filters_by_preferred_category_in_either_preference_list(): void
    {
        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->filterTable('prefers', $this->moty->id)
            ->assertCanSeeTableRecords([$this->strong])
            ->assertCanNotSeeTableRecords([$this->borderline])
            ->filterTable('prefers', $this->op->id)
            ->assertCanSeeTableRecords([$this->strong, $this->borderline]);
    }

    public function test_hosts_cannot_open_the_page_while_applicant_names_are_hidden(): void
    {
        $this->actingAs($this->host)
            ->get(JurorAllocations::getUrl())
            ->assertForbidden();
    }

    public function test_unallocated_list_only_has_applicants_without_any_allocation(): void
    {
        $page = Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->assertSee('Unallocated Applicants (1)');

        $instance = $page->instance();
        $unallocated = $instance->currentAllocations()['unallocated'];

        $this->assertSame(['Borderline Bea'], array_column($unallocated, 'name'));
    }

    public function test_changing_the_base_cutoff_updates_the_table_without_clearing_the_cache(): void
    {
        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->assertCanSeeTableRecords([$this->borderline])
            ->assertCanNotSeeTableRecords([$this->belowCutoff])
            ->set('baseCutoff', 2)
            ->assertCanNotSeeTableRecords([$this->borderline])
            ->set('baseCutoff', 1)
            ->assertCanSeeTableRecords([$this->borderline, $this->belowCutoff]);
    }

    public function test_applicants_below_the_base_cutoff_are_kept_if_they_answered_yes_to_both_choices(): void
    {
        $bothYes = User::factory()->create(['name' => 'Both Yes Yuki']);
        $this->applicant($bothYes, [1, 1], [], []);
        $this->answerChoices($bothYes, ['mc-1' => 'mc-1-yes', 'mc-2' => 'mc-2-yes']);

        $oneYes = User::factory()->create(['name' => 'One Yes Ola']);
        $this->applicant($oneYes, [1, 1], [], []);
        $this->answerChoices($oneYes, ['mc-1' => 'mc-1-yes', 'mc-2' => 'mc-2-no']);

        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->assertCanSeeTableRecords([$bothYes])
            ->assertCanNotSeeTableRecords([$oneYes, $this->belowCutoff]);
    }

    public function test_changing_the_base_cutoff_also_works_with_over_100_applicants(): void
    {
        $this->addFillerApplicants(100);

        // Checks the applicant list rather than the HTML: a failing assertSee would dump
        // the whole 100-row page, which takes the test runner minutes to print
        $page = Livewire::actingAs($this->admin)->test(JurorAllocations::class);
        $this->assertTrue($this->listsApplicant($page->instance(), $this->borderline), 'Borderline Bea should be listed at the default cutoff');

        $page->set('baseCutoff', 2);
        $this->assertFalse($this->listsApplicant($page->instance(), $this->borderline), 'Borderline Bea should be gone after raising the base cutoff to 2');
    }

    public function test_base_cutoff_keeps_yes_to_both_applicants_with_over_100_applicants(): void
    {
        $this->addFillerApplicants(100);

        $bothYes = User::factory()->create(['name' => 'Both Yes Yuki']);
        $this->applicant($bothYes, [1, 1], [], []);
        $this->answerChoices($bothYes, ['mc-1' => 'mc-1-yes', 'mc-2' => 'mc-2-yes']);

        $page = Livewire::actingAs($this->admin)->test(JurorAllocations::class)->instance();

        $this->assertTrue($this->listsApplicant($page, $bothYes), 'Both Yes Yuki should be kept despite a total below the base cutoff');
        $this->assertFalse($this->listsApplicant($page, $this->belowCutoff), 'Below Bo should still be cut');
    }

    /**
     * Over 100 applicants switches getAllApplicantsWithScores to its chunked path
     */
    private function addFillerApplicants(int $count): void
    {
        foreach (range(1, $count) as $index) {
            $this->applicant(User::factory()->create(), [3, 3], [], []);
        }
    }

    private function listsApplicant(JurorAllocations $page, User $applicant): bool
    {
        return in_array($applicant->id, array_column($page->getEligibleApplicants(), 'id'));
    }

    public function test_export_button_downloads_a_csv_named_after_the_export_date(): void
    {
        $this->travelTo('2026-10-26 12:00:00');

        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->callAction('export-csv')
            ->assertFileDownloaded('juror-allocations-2026-10-26.csv');
    }

    public function test_csv_lists_every_applicant_for_allocating_by_hand(): void
    {
        // User ID, UUID, name, empty Reddit / AniList ID (the factory sets neither), grading page link, then the rest
        $row = fn (User $user, string $name, string $rest) => "{$user->id},{$user->uuid},\"{$name}\",,,"
            .GradingPage::getUrlForUser($user->uuid).",{$rest}";

        $this->assertSame(
            "\xEF\xBB\xBF".implode("\r\n", [
                '"User ID",UUID,Applicant,Reddit,"AniList ID","Grading Page","Total Avg",Q1,"Q1 Grades",Q2,"Q2 Grades",'
                    .'"Above Base Cutoff","Anime of the Year","Movie of the Year",OP,Drama,'
                    .'"Main Preference 1","Main Preference 2","Doesn\'t Want","Non-Main Preferences","Website Allocation"',
                $row($this->strong, 'Strong Sam', '4,4,1,4,1,Yes,1,2,Yes,Yes,"Anime of the Year","Movie of the Year",,"OP; Drama",'
                    .'"Anime of the Year; Movie of the Year; OP"'),
                $row($this->borderline, 'Borderline Bea', '1.5,2,1,1,1,Yes,1,,Yes,,"Anime of the Year",,,OP,'),
                // Below the base cutoff: listed too, and never allocated by the website
                $row($this->belowCutoff, 'Below Bo', '1,1,1,1,1,No,1,,,,"Anime of the Year",,,,'),
            ])."\r\n",
            $this->exportCsv(),
        );
    }

    public function test_csv_has_a_column_per_category_with_the_rank_doesnt_want_or_yes(): void
    {
        $choosy = User::factory()->create(['name' => 'Choosy Cleo']);
        $this->applicant($choosy, [4, 4], [$this->aoty], [$this->op], unwantedMain: [$this->moty]);

        $row = $this->csvRows()[$choosy->id];

        $this->assertSame(
            ['1', "Doesn't want", 'Yes', ''],
            [$row['Anime of the Year'], $row['Movie of the Year'], $row['OP'], $row['Drama']],
        );
    }

    public function test_csv_has_contact_details_and_a_grading_page_link(): void
    {
        $contactable = User::factory()->create(['name' => 'Contact Cam', 'reddit_user' => 'contact_cam', 'anilist_id' => 4242]);
        $this->applicant($contactable, [4, 4], [$this->aoty], []);

        $row = $this->csvRows()[$contactable->id];

        $this->assertSame(
            ['contact_cam', '4242', GradingPage::getUrlForUser($contactable->uuid)],
            [$row['Reddit'], $row['AniList ID'], $row['Grading Page']],
        );
    }

    public function test_table_shows_the_main_categories_an_applicant_does_not_want(): void
    {
        $choosy = User::factory()->create(['name' => 'Choosy Cleo']);
        $this->applicant($choosy, [4, 4], [$this->aoty], [], unwantedMain: [$this->moty]);

        Livewire::actingAs($this->admin)
            ->test(JurorAllocations::class)
            ->assertTableColumnStateSet('unwanted_main_preferences', ['Movie of the Year'], $choosy)
            ->assertTableColumnStateSet('unwanted_main_preferences', null, $this->strong);
    }

    public function test_csv_shows_the_main_categories_an_applicant_does_not_want(): void
    {
        $choosy = User::factory()->create(['name' => 'Choosy Cleo']);
        $this->applicant($choosy, [4, 4], [$this->aoty], [], unwantedMain: [$this->moty]);

        $this->assertSame('Movie of the Year', $this->csvRows()[$choosy->id]["Doesn't Want"]);
    }

    public function test_an_empty_unwanted_list_in_any_stored_form_shows_as_none(): void
    {
        // null is what untouched answers stored before the backfill; '' and "[]" are the other empty forms
        $variants = [];
        foreach ([null, '', '[]'] as $index => $empty) {
            $variants[$index] = User::factory()->create();
            $this->applicantWithPreferences($variants[$index], [4, 4], [
                'non_main' => [],
                'main_order' => $this->orderJson([$this->aoty]),
                'no_main_order' => $empty,
            ]);
        }

        $page = Livewire::actingAs($this->admin)->test(JurorAllocations::class);

        foreach ($variants as $applicant) {
            $page->assertTableColumnStateSet('unwanted_main_preferences', null, $applicant);
        }
    }

    public function test_csv_escapes_names_that_look_like_formulas(): void
    {
        $formula = User::factory()->create(['name' => '=HYPERLINK("http://example.com")']);
        $this->applicant($formula, [4, 4], [], []);

        $this->assertStringContainsString('"\'=HYPERLINK(""http://example.com"")"', $this->exportCsv());
    }

    private function exportCsv(): string
    {
        $response = Livewire::actingAs($this->admin)->test(JurorAllocations::class)->instance()->exportCsv();

        ob_start();
        $response->sendContent();

        return ob_get_clean();
    }

    /**
     * The export as [user ID => [column header => value]]
     *
     * @return array<int, array<string, string>>
     */
    private function csvRows(): array
    {
        // Drop the byte order mark and the final row ending, then split rows on CRLF
        $lines = explode("\r\n", rtrim(substr($this->exportCsv(), 3), "\r\n"));
        $header = str_getcsv(array_shift($lines), escape: '');

        return collect($lines)
            ->map(fn (string $line) => array_combine($header, str_getcsv($line, escape: '')))
            ->keyBy('User ID')
            ->all();
    }

    private function answerChoices(User $applicant, array $optionIdsByQuestion): void
    {
        foreach ($optionIdsByQuestion as $questionId => $optionId) {
            AppAnswer::create(['applicant_id' => $applicant->id, 'question_id' => $questionId, 'answer' => $optionId]);
        }
    }

    private function category(string $name, string $type): Category
    {
        return Category::forceCreate(['year' => app('current-year'), 'name' => $name, 'type' => $type, 'entry_type' => 'anime']);
    }

    /**
     * Saves the preference answer and one score per essay, in the shape the application form
     * stores: the main ranking and the "don't want" list are each a JSON string inside the answer
     */
    private function applicant(User $applicant, array $scores, array $mainPreferences, array $nonMainPreferences, array $unwantedMain = []): void
    {
        $this->applicantWithPreferences($applicant, $scores, [
            'non_main' => collect($nonMainPreferences)->map(fn (Category $category) => (string) $category->id)->all(),
            'main_order' => $this->orderJson($mainPreferences),
            'no_main_order' => $this->orderJson($unwantedMain),
        ]);
    }

    // Categories as the form saves a ranking: a JSON string of [{category_id, order}]
    private function orderJson(array $categories): string
    {
        return json_encode(collect($categories)
            ->values()
            ->map(fn (Category $category, $index) => ['category_id' => (string) $category->id, 'order' => $index + 1])
            ->all());
    }

    // Saves the preference answer exactly as given, plus one score per essay
    private function applicantWithPreferences(User $applicant, array $scores, array $preferences): void
    {
        AppAnswer::create([
            'applicant_id' => $applicant->id,
            'question_id' => 'pref',
            'answer' => json_encode($preferences),
        ]);

        foreach (['essay-1', 'essay-2'] as $index => $questionId) {
            AppAnswer::create(['applicant_id' => $applicant->id, 'question_id' => $questionId, 'answer' => 'An answer']);
            AppScore::create([
                'applicant_id' => $applicant->id,
                'scorer_id' => $this->host->id,
                'question_id' => $questionId,
                'score' => $scores[$index],
                'comment' => '',
            ]);
        }
    }
}
