<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Result;
use App\Services\ResultService;
use Illuminate\View\View;

class ResultController extends Controller
{
    //
    public function index()
    {
        return response('<h1>Result Index Controller Function!</h1>', 200)->header('Content-Type', 'text/html');
    }

    public function archive(ResultService $resultservice)
    {
        $years = $resultservice->getYearList()->toJson();

        return response('<h1>Result Archive Controller Function!</h1><br>Years:<br>'.$years, 200)->header('Content-Type', 'text/html');
    }

    public function result(ResultService $resultservice, int $year)
    {
        // \DB::enableQueryLog();

        $results = $resultservice->getResults($year);

        // $queries = \DB::getQueryLog();
        // dd($queries);

        return view('results.result', [ 'year' => $year, 'categorylist' => $results ]);

    }

    public function acknowledgements(ResultService $resultservice, int $year)
    {

        $res = 'ACKNOWLEDGEMENT_PLACEHOLDER';


        return response('<h1>Yearly Acknowledgements Controller Function for '.$year.' </h1><br><br>'.$res, 200)->header('Content-Type', 'text/html');
    }

    public function about(int $year)
    {
        $res = 'ABOUT_PLACEHOLDER';

        return response('<h1>Yearly About Controller Function for '.$year.' </h1><br><br>'.$res, 200)->header('Content-Type', 'text/html');

    }
}
