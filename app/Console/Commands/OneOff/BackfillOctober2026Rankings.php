<?php

namespace App\Console\Commands\OneOff;

use App\Models\AppAnswer;
use App\Models\Application;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off: delete this command and its test (tests/Feature/OneOff) once it has run on prod.
 */
class BackfillOctober2026Rankings extends Command
{
    protected $signature = 'one-off:backfill-october-2026-rankings
        {--force : Save the changes; without it, only shows what would change}';

    protected $description = 'One-off: give October 2026 applicants who never dragged the main-category ranking the order the form showed them';

    // Only the October 2026 window is affected: the form showed main categories by id then, and
    // saved nothing without a drag. Later windows show the admin-set order and always save it
    private const YEAR = 2026;

    private const SAVED_FROM = '2026-10-01 00:00:00';

    private const SAVED_UNTIL = '2026-10-31 23:59:59';

    /**
     * The application form only saved its main-category ranking after a drag, so an applicant who
     * accepted the order shown submitted both lists empty. For answers last saved in October 2026,
     * this stores that shown order (main categories by id) in exactly the format the form's script
     * saves, leaving everything else in the answer and its timestamps as they were.
     */
    public function handle(): int
    {
        $year = self::YEAR;

        $application = Application::where('year', $year)->first();
        $preferenceQuestion = collect($application?->form ?? [])->firstWhere('type', 'preference');

        if (! $preferenceQuestion) {
            $this->error("No application with a preference question for {$year}.");

            return self::FAILURE;
        }

        // During the first 2026 window the form listed categories in id order (no explicit ordering),
        // so that's what these applicants saw. The form has since moved to the admin-set order
        $mainCategories = Category::where('year', $year)->where('type', 'main')->orderBy('id')->get(['id', 'name']);

        if ($mainCategories->isEmpty()) {
            $this->error("No main categories for {$year}.");

            return self::FAILURE;
        }

        // Same shape as the form script's JSON.stringify: category ids as strings, ranks from 1
        $shownOrder = json_encode($mainCategories->values()->map(fn ($category, $index) => [
            'category_id' => (string) $category->id,
            'order' => $index + 1,
        ])->all());

        $untouched = AppAnswer::where('question_id', $preferenceQuestion['id'])
            ->whereBetween('updated_at', [self::SAVED_FROM, self::SAVED_UNTIL])
            ->orderBy('applicant_id')
            ->get()
            ->filter(function (AppAnswer $answer) {
                $preferences = json_decode($answer->answer, true);

                return is_array($preferences)
                    && $this->isEmptyList($preferences['main_order'] ?? '')
                    && $this->isEmptyList($preferences['no_main_order'] ?? '');
            });

        $this->line('Order shown: '.$mainCategories->pluck('name')->map(fn ($name, $index) => ($index + 1).'. '.$name)->implode(', '));
        $this->line("Untouched rankings saved in October 2026: {$untouched->count()}");

        if ($untouched->isEmpty()) {
            return self::SUCCESS;
        }

        $this->line('Applicant ids: '.$untouched->pluck('applicant_id')->implode(', '));

        if (! $this->option('force')) {
            $this->warn('Dry run, nothing saved. Run again with --force to save.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($untouched, $shownOrder) {
            foreach ($untouched as $answer) {
                $preferences = json_decode($answer->answer, true);
                $preferences['main_order'] = $shownOrder;
                $preferences['no_main_order'] = '[]';

                // Keep updated_at as the applicant's own last save
                $answer->timestamps = false;
                $answer->answer = json_encode($preferences);
                $answer->save();
            }
        });

        $this->info("Saved the shown order for {$untouched->count()} applicants.");

        return self::SUCCESS;
    }

    // Empty whether it was never set (''), saved as "[]", or stored as an actual empty array
    private function isEmptyList(mixed $list): bool
    {
        if (is_array($list)) {
            return $list === [];
        }

        return $list === null || $list === '' || json_decode($list, true) === [];
    }
}
