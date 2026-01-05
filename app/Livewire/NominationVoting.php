<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\CategoryEligible;
use App\Models\Entry;
use App\Models\NomineeVote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class NominationVoting extends Component
{
    public $loaded = false;

    public function mount()
    {
        $this->loaded = true;
    }

    #[Computed]
    public function groups()
    {
        return [
            ['slug' => 'main', 'text' => 'Main Awards'],
            ['slug' => 'genre', 'text' => 'Genre Awards'],
            ['slug' => 'production', 'text' => 'Production Awards'],
            ['slug' => 'character', 'text' => 'Character Awards'],
        ];
    }

    #[Computed]
    public function categories()
    {
        return Category::where('year', app('current-year'))
            ->has('eligibles')
            ->get()
            ->groupBy('type');
    }

    #[Computed]
    public function selections()
    {
        $votes = NomineeVote::where('user_id', $this->user->id)
            ->with('entry')
            ->get();

        return $votes->groupBy('category_id')
            ->map(function ($categorySelections) {
                return $categorySelections->keyBy('cat_entry_id');
            });
    }

    public function fetchEligibles($selectedCategoryId)
    {
        // Set items as empty collection if category not present
        if (! $selectedCategoryId) {
            return collect();
        }

        // TODO: Change query to search for character categories
        // Set limit to 50 if category type is character
        // $limit = 0;
        // if($this->selectedCategory['type'] == 'character') {
        //     $limit = 50;
        // }

        // TODO: Implement cache
        return $this->getEligiblesByCategory($selectedCategoryId);
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

    private function getEligiblesByCategory(int $categoryId, int $limit = 0): Collection
    {
        $query = CategoryEligible::withWhereHas('entry', function ($query) {
            $query->select('id', 'name', 'image');
        })
            ->where('category_id', $categoryId)
            ->where('active', true)
            ->select('id', 'category_id', 'entry_id');

        if ($limit > 0) {
            $query = $query->limit($limit);
        }

        return $query->get();
    }

    private function getEntryById(int $entryId): Entry
    {
        return Entry::find($entryId);
    }

    public function render()
    {
        return view('livewire.nomination-voting');
    }
}
