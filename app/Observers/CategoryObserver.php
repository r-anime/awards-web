<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        //
        $this->clearResultYearCache($category);
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        //
        $this->clearResultYearCache($category);
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        //
        $this->clearResultYearCache($category);
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        //
        $this->clearResultYearCache($category);
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        //
        $this->clearResultYearCache($category);
    }

    private function clearResultYearCache(Category $category): void
    {
        Cache::forget('results_'.$category->year);
    }
}
