<?php

namespace App\Observers;

use App\Models\Result;
use Illuminate\Support\Facades\Cache;

class ResultObserver
{
    /**
     * Handle the Result "created" event.
     */
    public function created(Result $result): void
    {
        //
        $this->clearResultYearCache($result);
    }

    /**
     * Handle the Result "updated" event.
     */
    public function updated(Result $result): void
    {
        //
        $this->clearResultYearCache($result);
    }

    /**
     * Handle the Result "deleted" event.
     */
    public function deleted(Result $result): void
    {
        //
        $this->clearResultYearCache($result);
    }

    /**
     * Handle the Result "restored" event.
     */
    public function restored(Result $result): void
    {
        //
        $this->clearResultYearCache($result);
    }

    /**
     * Handle the Result "force deleted" event.
     */
    public function forceDeleted(Result $result): void
    {
        //
        $this->clearResultYearCache($result);
    }

    private function clearResultYearCache(Result $result): void
    {
        Cache::forget('results_'.$result->year);
    }
}
