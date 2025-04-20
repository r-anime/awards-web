<?php

namespace App\Livewire;

use Filament\Notifications\Notification;
use Livewire\Component;

class YearFilter extends Component
{
    public $yearList;
    public $filteredYear;

    public function mount()
    {
        $this->yearList = array(2021, 2022, 2023, 2024, 2025);
    }

    public function updated($property)
    {
        if ($property == 'filteredYear') {
            session(['selected-year-filter' => $this->filteredYear]); //$this->filteredYear
            $this->dispatch('filter-year-updated');
        }
    }

    public function render()
    {
        $this->filteredYear = session('selected-year-filter') ?? 2025;
        return view('livewire.year-filter');
    }
}
