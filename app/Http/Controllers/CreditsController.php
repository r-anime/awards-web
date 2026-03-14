<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class CreditsController extends Controller
{
    public function __invoke()
    {
        $markdown = config('credits.content', '');
        $html = $markdown !== '' ? Str::markdown($markdown) : '';

        return view('credits', ['content' => $html]);
    }
}
