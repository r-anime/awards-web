<?php

namespace App\Livewire;

use Livewire\Component;

class YearFilter extends Component
{
    public $selectedYear;
    public $yearList;

    public function mount()
    {
        $this->selectedYear = session('selected-year-filter') ?? app('current-year');
        $this->yearList = $this->generateYearList();
    }

    public function updatedSelectedYear()
    {
        session(['selected-year-filter' => $this->selectedYear]);
        $this->dispatch('filter-year-updated');
    }

    private function generateYearList()
    {
        $currentYear = app('current-year');
        $years = [];
        
        for ($year = $currentYear; $year >= 2016; $year--) {
            $years[] = $year;
        }
        
        return $years;
    }

    public function render()
    {
        return view('livewire.year-filter');
    }
}
