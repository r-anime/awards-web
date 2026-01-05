<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use App\Models\Category;
use App\Models\CategoryEligible;
use App\Models\Entry;
use App\Models\NomineeVote;
use App\Models\Option;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Illuminate\Support\Collection;

#[Layout('components.layouts.app')]
class NominationVoting extends Component
{
    public $loaded=false;

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
            ->map(function($categorySelections) {
                return $categorySelections->keyBy('cat_entry_id');
        });
    }
    
    public function fetchEligibles($selectedCategoryId)
    {
        // Set items as empty collection if category not present
        if (!$selectedCategoryId) {
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

    private function loadVotes()
    {
        // Load user's existing votes (only non-deleted ones)
        $votes = NomineeVote::where('user_id', $this->user->id)
            ->with('entry')
            ->get();
        
        foreach ($votes as $vote) {
            if (isset($this->selections[$vote->category_id])) {
                $entry = $vote->entry;
                if ($entry) {
                    $this->selections[$vote->category_id][] = [
                        'id' => $entry->id,
                        'anilist_id' => $entry->anilist_id,
                        'name' => $entry->name,
                        'image' => $entry->image,
                        'type' => $entry->type,
                    ];
                }
            }
        }
        
        // Ensure selections are Collections
        foreach ($this->selections as $categoryId => $selections) {
            if (!$selections instanceof Collection) {
                $this->selections[$categoryId] = collect();
            }
        }
    }

    private function updatedSelectedGroup()
    {
        $this->updateVotingCategories();
        if (!empty($this->votingCategories)) {
            $this->selectedCategoryId = $this->votingCategories[0]['id'];
        } else {
            $this->selectedCategoryId = null;
        }

        $this->search = '';
        $this->updateSelectedCategory();
        $this->updateCategoryEntries();
        
        // Clear cache when switching groups
        $this->categoryItemsCache = [];
        $this->updateFilteredItems();

        $this->updateProgress();
    }
    
    /**
     * Handle user click on group
     */
    public function setSelectedGroup($group)
    {
        \Log::info('setSelectedGroup called', ['group' => $group]);
        $this->selectedGroup = $group;
        $this->updatedSelectedGroup();
    }
    
    private function updateVotingCategories()
    {
        $this->votingCategories = $this->categories
            ->where('type', $this->selectedGroup)
            ->values();
    }
    
    /**
     * Handle user click on category
     */
    public function selectCategory($categoryId)
    {
        \Log::info('selectCategory called', ['categoryId' => $categoryId]);
        $this->selectedCategoryId = $categoryId;
        $this->search = '';
        $this->updateSelectedCategory();

        $this->updateCategoryEntries();
        
        // Clear cache for this category to force refresh if needed
        unset($this->categoryItemsCache[$categoryId]);
        $this->updateFilteredItems();
    }
    
    private function updateSelectedCategory()
    {
        if (!$this->selectedCategoryId) {
            $this->selectedCategory = null;
            return;
        }
        
        // Find the category from the categories arrayx
        $this->selectedCategory = $this->categories->firstWhere('id', $this->selectedCategoryId);
    }
    
    #[Computed]
    public function getCategoryItemsProperty() {
        
        // Set items as empty collection if category not present
        if (!$this->selectedCategoryId || !$this->selectedCategory) {
            return collect();
        }

        // TODO: Change query to search for character categories
        // Set limit to 50 if category type is character
        $limit = 0;
        if($this->selectedCategory['type'] == 'character') {
            $limit = 50;
        }
        
        // TODO: Implement cache
        return $this->getEligiblesByCategory($this->selectedCategoryId, $limit)->pluck('entry');
    }

    private function updateCategoryEntries()
    {
        // Set items as empty collection if category not present
        if (!$this->selectedCategoryId || !$this->selectedCategory) {
            $this->categoryEntries = collect();
            return;
        }

        // TODO: Change query to search for character categories
        // Set limit to 50 if category type is character
        $limit = 0;
        if($this->selectedCategory['type'] == 'character') {
            $limit = 50;
        }
        
        // TODO: Implement cache
        $this->categoryEntries = $this->getEligiblesByCategory($this->selectedCategoryId, $limit)->pluck('entry');
    }

    /**
     * Set the filteredItems property as per category and search
     */
    private function updateFilteredItems() : void
    {
        $entries = $this->categoryEntries;
        $this->filteredItems = $this->categoryEntries;
        return; 
        if (empty($this->search)) {
            // Sort: selected items first 
            // TODO: Push this to frontend ideally? Because this re-fetch is 
            $selectedIds = ($this->selections[$this->selectedCategoryId] ?? collect())->pluck('id');
            $sortedEntries = $entries->sort(function ($a, $b) use ($selectedIds, $entries) {
                if($a == null) {
                    dd($entries);
                }
                $aSelected = $selectedIds->contains($a['id']);
                $bSelected = $selectedIds->contains($b['id']);
                if ($aSelected === $bSelected) {
                    return 0;
                }
                return $aSelected ? -1 : 1;
            });
            $this->filteredItems = $sortedEntries;
        } else {
            $searchLower = strtolower($this->search);
            $this->filteredItems = $entries->filter(function ($entry) use ($searchLower) {
                $name = strtolower($entry['name'] ?? '');
                return str_contains($name, $searchLower);
            });
            // Re-index array
            // $this->filteredItems = $this->filteredItems;
        }
    }
    
    public function updatedSearch()
    {
        $this->updateFilteredItems();
    }
    
    public function isItemSelected($item)
    {
        if (!$this->selectedCategoryId) {
            return false;
        }
        
        $selections = $this->selections[$this->selectedCategoryId] ?? collect();
        return $selections->contains(function ($selection) use ($item) {
            return isset($selection['id']) && $selection['id'] == $item['id'];
        });
    }
    
    #[Computed]
    public function user() {
        return Auth::user();
    }

    private function canVoteMore($categoryId)
    {
        $count = NomineeVote::where('user_id', $this->user->id)
            ->where('category_id', $categoryId)
            ->count();
        return $count < 5;
    }
    
    public function toggleVote($entryId)
    {
        if (!$this->selectedCategoryId) {
            return;
        }
        
        $item = $this->getEntryById($entryId);
        if (!$item) {
            return;
        }
        
        $isSelected = $this->isItemSelected($item);
        
        if ($isSelected) {
            $this->removeVote($entryId);
        } else {
            $this->addVote($entryId);
        }
    }
    
    public function createVote($categoryId, $eligibleId, $entryId)
    {
        if (!$this->canVoteMore($categoryId)) {
            session()->flash('error', 'You cannot vote for any more entries in this category.');
            return response()->json(['error' => 'Cannot vote for any more entries in this category'], 400); // Failed to validate eligible
        }
        
        // Find the category eligible entry
        $eligible = CategoryEligible::find($eligibleId);
        if(!$eligible 
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
        
        if(!$vote) {
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

        if(!$deleted) {
            return response()->json(['error' => 'Error deleting vote'], 500);
        }

        $this->dispatch('vote-removed');
        return response()->json(['success' => 'Vote deleted']);

    }
    
    /**
     * Set `$this->progress` as number of categories user has voted in
     */
    private function updateProgress()
    {
        // Count how many categories across all groups the user has voted in
        $this->progress = $this->selections
            ->filter(function ($selections, $categoryId) {
                return !empty($selections) && count($selections) > 0;
            })
            ->count();
    }
    
    /**
     * Returns array of categories that have been voted on
     */
    public function getVotedCategoriesProperty()
    {
        $voted = [];
        foreach ($this->selections as $categoryId => $selections) {
            $voted[$categoryId] = !empty($selections);
        }
        return $voted;
    }

    // DB Calls
    private function getCategories() : Collection
    {
        return Category::where('year', app('current-year'))->get();
    }
    
    private function getEligiblesByCategory(int $categoryId, int $limit = 0) : Collection
    {
        $query = CategoryEligible::withWhereHas('entry', function($query) {
            $query->select('id', 'name', 'image');
        })
            ->where('category_id', $categoryId)
            ->where('active', true)
            ->select('id', 'category_id', 'entry_id');

        if($limit > 0) {
            $query = $query->limit($limit);
        }
        return $query->get();
    }

    private function getEntryById(int $entryId) : Entry
    {
        return Entry::find($entryId);
    }

    public function render()
    {
        return view('livewire.nomination-voting');
    }
}

