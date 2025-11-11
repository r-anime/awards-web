<?php

namespace App\Http\Controllers;

use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    //
    public function index()
    {
        return response('<h1>Result Index Controller Function!</h1>', 200)->header('Content-Type', 'text/html');
    }

    public function archive()
    {

        $years = Result::select('year')->groupBy('year')->get();
        return response('<h1>Result Archive Controller Function!</h1><br>Years:<br>'.$years->toJson(), 200)->header('Content-Type', 'text/html');

    }

    public function result(int $year)
    {
        return response('<h1>Yearly Result Controller Function for '.$year.' </h1>', 200)->header('Content-Type', 'text/html');

    }


    public function acknowledgements(int $year)
    {
        return response('<h1>Yearly Acknowledgements Controller Function for '.$year.' </h1>', 200)->header('Content-Type', 'text/html');

    }

    public function about(int $year)
    {
        return response('<h1>Yearly About Controller Function for '.$year.' </h1>', 200)->header('Content-Type', 'text/html');

    }
}
