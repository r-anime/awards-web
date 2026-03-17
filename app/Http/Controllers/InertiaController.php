<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class InertiaController extends Controller
{
    //
    public function test() {
        return Inertia::render('InertiaTest', ['username' => 'Jesus' ]);
    }

    /**
     * Redirect to the Inertia results page for the latest visible year.
     */
    public function latestResults(ResultService $resultservice)
    {
        $isHostOrHigher = auth()->check() && (int) auth()->user()->role >= 2;
        $visibleYears = $isHostOrHigher
            ? $resultservice->getYearList()->pluck('year')->sortDesc()->values()->all()
            : $resultservice->getVisibleYearList();
        $latestYear = $visibleYears[0] ?? null;
        if ($latestYear === null) {
            abort(404);
        }
        return redirect()->route('inertia.results', ['year' => $latestYear]);
    }

    public function results(ResultService $resultservice, int $year) {
        $isHostOrHigher = auth()->check() && (int) auth()->user()->role >= 2;
        if (!$isHostOrHigher && !$resultservice->isYearVisible($year)) {
            abort(404);
        }
        $results = $resultservice->getResults($year)->groupBy('type');
        return Inertia::render('Results/Results',
            ['year' => $year,
            'result' => $results]);
    }

    public function acknowledgements(ResultService $resultservice, int $year) {
        $acknowledgements = $resultservice->getAcknowledgements($year);
        // dd($acknowledgements);
        return Inertia::render('Results/Acknowledgements',
        [
            'year' => $year,
            'acknowledgements' => $acknowledgements
        ]);
    }

}
