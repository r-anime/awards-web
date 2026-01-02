<?php

namespace App\Livewire;

use Livewire\Component;
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
    /**
     * Currently selected category group
     */
    public $selectedGroup = 'main';
    /**
     * Currently selected category
     */
    public $selectedCategoryId = null;
    public $search = '';
    public $loaded = false;
    public $locked = false;
    public $loadingProgress = ['curr' => 0, 'max' => 0];
    
    /**
     * Collection of all categories
     */
    public $categories = [];
    /**
     * Categories under the currently selected Group
     */
    public $votingCategories = [];
    
    /**
     * Collection of all Eligibles under current category
     */
    public $currentEligibles = [];

    /**
     * Collection of all selected Entries
     */
    public $selections = [];
    public $user = null;
    /**
     * Number of categories voted on
     */
    public $progress = 0;
    public $selectedCategory = null;
    /**
     * Unfiltered entries under current category
     */
    public $categoryEntries = [];
    /**
     * categoryEntries filtered by search
     */
    public $filteredItems = [];
    /**
     * Cached items per category
     */
    public $categoryItemsCache = []; 

    public function mount()
    {
        $this->user = Auth::user();
        
        if (!$this->user) {
            return $this->redirect('/login', navigate: true);
        }
        
        // Check if nomination voting is currently open
        $startDate = Option::get('nomination_voting_start_date', '');
        $endDate = Option::get('nomination_voting_end_date', '');
        
        // If dates are set, check if current time is within the voting period
        if (!empty($startDate) && !empty($endDate)) {
            $now = Carbon::now();
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            
            // If outside the voting period and user doesn't have role >= 2, deny access
            if (!$now->between($start, $end) && $this->user->role < 2) {
                abort(403, 'Nomination voting is not currently open.');
            }
        }
        
        // Clear any stored redirect URL since we're already on the intended page
        if (session()->has('redirect_after_login')) {
            session()->forget('redirect_after_login');
        }
        
        // ---
        // Load data
        // ---

        // Load Categories
        $this->categories = $this->getCategories();

        // Initialize selections structure
        $this->selections = $this->categories->reduce(function ($carry, $category) {
            return $carry->put($category['id'], collect());
        }, collect());

        $this->loadVotes();
        
        // ---
        // Set initial state
        // ---
        
        // Filter categories by selected group
        $this->updateVotingCategories();

        // Set initial selected category
        if (!empty($this->votingCategories)) {
            $this->selectedCategoryId = $this->votingCategories[0]['id'];
        } else {
            // If no categories found, still mark as loaded
            $this->selectedCategoryId = null;
        }

        // Update selected category object
        $this->updateSelectedCategory();
        
        // Update fetched entries for category
        $this->updateCategoryEntries();

        // Update filtered items
        $this->updateFilteredItems();
        
        // Calculate initial progress
        $this->updateProgress();
        
        $this->loaded = true;
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
        // $startTime = microtime(true);
        $this->updateVotingCategories();
        // $updatedVotingCategoriesTime = microtime(true) - $startTime;
        if (!empty($this->votingCategories)) {
            $this->selectedCategoryId = $this->votingCategories[0]['id'];
        } else {
            $this->selectedCategoryId = null;
        }
        $this->search = '';
        $this->updateSelectedCategory();
        // $updatedSelectedCategoriesTime = microtime(true) - $startTime + $updatedVotingCategoriesTime;
        // Clear cache when switching groups
        $this->categoryItemsCache = [];
        $this->updateFilteredItems();
        // $updatedFilteredItemsTime = microtime(true) -$startTime + $updatedSelectedCategoriesTime + $updatedVotingCategoriesTime;
        $this->updateProgress();
        // dd($startTime*1000, $updatedVotingCategoriesTime*1000, $updatedSelectedCategoriesTime*1000, $updatedFilteredItemsTime*1000);
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
    
    private function getEntryType($category)
    {
        $name = strtolower($category['name'] ?? '');
        $type = $category['type'] ?? '';
        
        if (str_contains($name, 'ost')) {
            return 'ost';
        }
        if (str_contains($name, 'op') || str_contains($name, 'ed') || str_contains($name, 'theme')) {
            return 'theme';
        }
        if ($type === 'character') {
            if (str_contains($name, 'va') || str_contains($name, 'voice')) {
                return 'va';
            }
            return 'character';
        }
        
        return 'anime';
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
    
    private function canVoteMore()
    {
        if (!$this->selectedCategoryId) {
            return false;
        }
        
        $count = count($this->selections[$this->selectedCategoryId] ?? []);
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
    
    private function addVote($entryId)
    {
        if (!$this->canVoteMore()) {
            session()->flash('error', 'You cannot vote for any more entries in this category.');
            return;
        }
        
        $item = $this->getEntryById($entryId);
        if (!$item) {
            return;
        }
        
        if ($this->isItemSelected($item)) {
            return;
        }
        
        // Find the category eligible entry
        $categoryEligible = CategoryEligible::where('category_id', $this->selectedCategoryId)
            ->where('entry_id', $entryId)
            ->first();
        
        if (!$categoryEligible) {
            // Fail vote creation if entry does not exist
            \Log::notice('Failed to create vote, category_eligible record not found ',
                ['category_id' => $this->selectedCategoryId, 'entry_id' => $entryId]);
            return;
        }
        
        // Check if vote already exists
        $existingVote = NomineeVote::where('user_id', $this->user->id)
            ->where('category_id', $this->selectedCategoryId)
            ->where('entry_id', $entryId)
            ->first();
        
        if ($existingVote) {
            return; // Vote already exists
        }
        
        // Create the vote
        NomineeVote::create([
            'user_id' => $this->user->id,
            'category_id' => $this->selectedCategoryId,
            'cat_entry_id' => $categoryEligible->id,
            'entry_id' => $entryId,
        ]);
        
        // Update local selections
        $this->selections[$this->selectedCategoryId][] = [
            'id' => $item['id'],
            'anilist_id' => $item['anilist_id'] ?? null,
            'name' => $item['name'],
            'image' => $item['image'] ?? null,
            'type' => $item['type'],
        ];
        
        $this->updateFilteredItems();
        $this->updateProgress();
        $this->dispatch('vote-added');
    }
    
    /**
     * Deletes vote, updates local selections,
     * Calls `updateFilteredItems()` and `updateProgress()`,
     * Dispatches `vote-removed`
     * @param id $entryId Id of the item from which the vote is to be removed
     */
    public function removeVote($entryId)
    {
        if (!$this->selectedCategoryId) {
            return;
        }
        
        // Delete the vote
        NomineeVote::where('user_id', $this->user->id)
            ->where('category_id', $this->selectedCategoryId)
            ->where('entry_id', $entryId)
            ->delete();
        
        // Update local selections
        $selections = $this->selections[$this->selectedCategoryId] ?? collect();
        $this->selections[$this->selectedCategoryId] = $selections
            ->reject(function ($selection) use ($entryId) {
                return $selection['id'] == $entryId;
            });
        
        $this->updateFilteredItems();
        $this->updateProgress();
        $this->dispatch('vote-removed');
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
        $query = CategoryEligible::where('category_id', $categoryId)
            ->where('active', true)
            ->has('entry') // Only pull eligibles when entry exists
            ->with('entry');
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

