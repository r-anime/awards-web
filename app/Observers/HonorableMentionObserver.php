<?php

namespace App\Observers;

use App\Models\HonorableMention;
use Illuminate\Support\Facades\Cache;

class HonorableMentionObserver
{
    /**
     * Handle the HonorableMention "created" event.
     */
    public function created(HonorableMention $honorableMention): void
    {
        //
        $this->clearResultYearCache($honorableMention);
    }

    /**
     * Handle the HonorableMention "updated" event.
     */
    public function updated(HonorableMention $honorableMention): void
    {
        //
        $this->clearResultYearCache($honorableMention);
    }

    /**
     * Handle the HonorableMention "deleted" event.
     */
    public function deleted(HonorableMention $honorableMention): void
    {
        //
        $this->clearResultYearCache($honorableMention);
    }

    /**
     * Handle the HonorableMention "restored" event.
     */
    public function restored(HonorableMention $honorableMention): void
    {
        //
        $this->clearResultYearCache($honorableMention);
    }

    /**
     * Handle the HonorableMention "force deleted" event.
     */
    public function forceDeleted(HonorableMention $honorableMention): void
    {
        //
        $this->clearResultYearCache($honorableMention);
    }

    private function clearResultYearCache(HonorableMention $hm): void
    {
        Cache::forget('results_'.$hm->year);
    }
}
