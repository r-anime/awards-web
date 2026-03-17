<?php

namespace App\Services;

use App\Models\Acknowledgement;
use App\Models\Category;
use App\Models\Option;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ResultService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getYearList(): Collection
    {
        // Get list of years for which results are available
        // TODO(?): Separate into queries for Acknowledgements and About
        $years = Cache::remember('results_yearlist', 10800, function() {
            return Result::select('year')->groupBy('year')->get();
        });
        return $years;
    }

    /**
     * Get list of years that should be shown publicly. Only the year that matches the
     * "final results" date - 1 year is hidden until that date/time has passed.
     */
    public function getVisibleYearList(): array
    {
        $years = $this->getYearList()->pluck('year')->sortDesc()->values()->all();
        if (empty($years)) {
            return [];
        }
        $displayDate = Option::get('results_display_date', '');
        if ($displayDate === '' || $displayDate === null) {
            return $years;
        }
        $displayAt = Carbon::parse($displayDate);
        // Lock the year *before* the display date
        $hiddenYear = (int) $displayAt->year - 1;
        if (now()->lt($displayAt)) {
            return array_values(array_filter($years, fn ($y) => (int) $y !== $hiddenYear));
        }
        return $years;
    }

    /**
     * Whether the given year is visible (can be shown on the site).
     */
    public function isYearVisible(int $year): bool
    {
        $displayDate = Option::get('results_display_date', '');
        if ($displayDate === '' || $displayDate === null) {
            return true;
        }
        $displayAt = Carbon::parse($displayDate);
        $hiddenYear = (int) $displayAt->year - 1;
        if ($year !== $hiddenYear) {
            return true;
        }
        return now()->gte($displayAt);
    }

    public function getResults(int $year): Collection
    {
        // Results by year
        // TODO: Implement cache
        $results = Cache::remember('results_'.$year, 10080, function () use ($year) {
        return Category::where('year', $year)
            ->with([
                'info' => function ($query) {
                    $query->select(['category_id', 'description']);
                },
                'results' => function ($query) {
                    $query->select([
                        'id',
                        'category_id',
                        'name',
                        'image',
                        'jury_rank',
                        'public_rank',
                        'description',
                        'staff_credits',
                        'entry_id',
                    ])
                        ->with(['entry' => function ($query) {
                            $query->select(['id', 'name', 'parent_id'])
                                ->with(['parent' => function ($query) {
                                    $query->select(['id', 'name', 'parent_id'])
                                    ->with(['parent' => function ($query) {
                                        $query->select(['id', 'name']);
                                    }]);
                                }]);
                    }])
                        ->orderBy('jury_rank');
                },
                'honorablementions' => function ($query) {
                    $query->select([
                        'id',
                        'category_id',
                        'name',
                        'writeup'
                    ]);
                },
            ])
            ->select([
                'id',
                'year',
                'name',
                'type',
                'order',
                'entry_type',
            ])
            ->orderBy('order')
            ->get();
        });

        return $results;
    }

    public function getAcknowledgements(int $year)
    {
        // Probably don't need cache
        $acknowledgements = Acknowledgement::where('year', $year)->get();
        return $acknowledgements;
    }

    public function getAbout(int $year)
    {
        // Paused on this until abouts are uploaded to db
        abort(404);
    }

    // For Results, Acks and Abouts, return the years that exist
    public function getArchive()
    {
        $results = Result::select('year')
            ->distinct()
            ->orderBy('year', 'asc')
            ->pluck('year');

        // Todo: Acks, Abouts
    }
}
