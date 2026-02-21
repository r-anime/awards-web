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

    public function result(ResultService $resultservice, int $year) {
        $results = $resultservice->getResults($year)->groupBy('type');
        return Inertia::render('Results/Index',
            ['year' => $year,
            'result' => $results]);
    }

}
