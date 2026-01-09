<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\CategoryEligible;
use App\Models\Entry;
use App\Models\NomineeVote;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class NominationVoting extends Component
{
    public $loaded = false;

    public $displayLimit = 25;

    public function mount()
    {
        $this->loaded = true;
    }

    #[Computed]
    public function groups()
    {
        return [
            ['slug' => 'genre', 'text' => 'Genre Awards'],
            ['slug' => 'production', 'text' => 'Production Awards'],
            ['slug' => 'character', 'text' => 'Character Awards'],
            ['slug' => 'main', 'text' => 'Main Awards'],
        ];
    }

    #[Computed]
    public function categories()
    {
        return Category::where('year', app('current-year'))
            ->has('eligibles')
            ->orderBy('order')
            ->get()
            ->groupBy('type');
    }

    #[Computed]
    public function selections()
    {
        $votes = NomineeVote::where('user_id', $this->user->id)
            // ->with('entry')
            ->get();

        return $votes->groupBy('category_id')
            ->map(function ($categorySelections) {
                return $categorySelections->keyBy('cat_entry_id');
            });
    }

    // TODO: Add Cache before going live
    public function fetchEntriesByType($entryType)
    {
        return Entry::select('id', 'name', 'image', 'parent_id')
        ->whereHas('category_eligibles.category', function ($query) use ($entryType) {
            $query->where('entry_type', $entryType);
        })->get();
    }

    // Unused so far
    public function fetchEntriesByIds($ids)
    {
        return Entry::select('id', 'name', 'image')
        ->whereIn('id', $ids)
        ->get();
    }
    
    // TODO: Add Cache before going live
    public function fetchEligibles($selectedCategoryId)
    {
        // Set items as empty collection if category not present
        if (! $selectedCategoryId) {
            return collect();
        }

        return CategoryEligible::select('id', 'category_id', 'entry_id')
        ->where('category_id', $selectedCategoryId)
        ->where('active', true)
        ->has('entry')
        ->get();
    }

    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    private function canVoteMore($categoryId)
    {
        $count = NomineeVote::where('user_id', $this->user->id)
            ->where('category_id', $categoryId)
            ->count();

        return $count < 5;
    }

    public function createVote($categoryId, $eligibleId, $entryId)
    {
        if (! $this->canVoteMore($categoryId)) {
            session()->flash('error', 'You cannot vote for any more entries in this category.');

            return response()->json(['error' => 'Cannot vote for any more entries in this category'], 400); // Failed to validate eligible
        }

        // Find the category eligible entry
        $eligible = CategoryEligible::find($eligibleId);
        if (! $eligible
            || $eligible->category_id != $categoryId
            || $eligible->entry_id != $entryId) {
            return response()->json(['error' => 'Invalid Eligible'], 400); // Failed to validate eligible
        }

        // Check if vote already exists
        $existingVote = NomineeVote::where('user_id', $this->user->id)
            ->where('category_id', $categoryId)
            ->where('entry_id', $entryId)
            ->first();

        if ($existingVote) {
            return response()->json(['error' => 'Vote already exists'], 400); // Vote already exists
        }

        // Create the vote
        $vote = NomineeVote::create([
            'user_id' => $this->user->id,
            'category_id' => $categoryId,
            'cat_entry_id' => $eligibleId,
            'entry_id' => $entryId,
        ]);

        if (! $vote) {
            return response()->json(['error' => 'Failed to create Vote'], 500); // Vote already exists
        }
        $this->dispatch('vote-added');

        return response()->json(['success' => 'Vote created']);
    }

    public function deleteVote($categoryId, $eligibleId, $entryId)
    {
        // Delete the vote
        $deleted = NomineeVote::where('user_id', $this->user->id)
            ->where('category_id', $categoryId)
            ->where('entry_id', $entryId)
            ->delete();

        if (! $deleted) {
            return response()->json(['error' => 'Error deleting vote'], 500);
        }

        $this->dispatch('vote-removed');

        return response()->json(['success' => 'Vote deleted']);

    }

    public function render()
    {
        return view('livewire.nomination-voting');
    }
}
