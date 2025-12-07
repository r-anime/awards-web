<?php

namespace App\Services;

use App\Models\Category;
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
                    ])
                        ->orderBy('jury_rank');
                },
            ])
            ->select([
                'id',
                'year',
                'name',
                'type',
                'order',
            ])
            ->get();
        });

        return $results;
    }

    public function getAcknowledgements(int $year)
    {
        // Paused on this until acknowledgements are uploaded to db
        abort(404);
    }

    public function getAbout(int $year)
    {
        // Paused on this until abouts are uploaded to db
        abort(404);
    }
}
