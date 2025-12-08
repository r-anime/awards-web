<?php

namespace App\View\Components\Results;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Js;

class ResultCategory extends Component
{
    public $jury_winner;
    /**
     * Create a new component instance.
     */
    public function __construct(public $resultcategory)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.results.result-category');
    }
}
