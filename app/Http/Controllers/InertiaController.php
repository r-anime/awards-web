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

    public function results(ResultService $resultservice, int $year) {
        if($year >= app('current-year')) {
            abort(404);
        }
        $results = $resultservice->getResults($year)->groupBy('type');
        return Inertia::render('Results/Results',
            ['year' => $year,
            'result' => $results]);
    }

    public function acknowledgements() {
        
    }

}
