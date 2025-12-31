<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Models\CategoryEligible;
use App\Models\Entry;
use App\Models\NomineeVote;
use App\Models\Option;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('components.layouts.app')]
class NominationVoting extends Component
{
    
    public $selectedGroup = 'main';
    public $selectedCategoryId = null;
    public $search = '';
    public $loaded = false;
    public $locked = false;
    public $loadingProgress = ['curr' => 0, 'max' => 0];
    
    public $categories = [];
    public $votingCategories = [];
    public $entries = [];
    public $items = [];
    public $selections = [];
    public $user = null;
    public $progress = 0;
    public $selectedCategory = null;
    public $filteredItems = [];
    public $categoryItemsCache = []; // Cache items per category
    
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
        
        $this->loadData();
    }
    
    public function loadData()
    {
        $year = date('Y');
        
        // Load all categories for the current year
        $this->categories = Category::where('year', $year)->get()->toArray();
        
        // Load entries (category eligibles)
        $categoryIds = collect($this->categories)->pluck('id')->toArray();
        $eligibles = CategoryEligible::whereIn('category_id', $categoryIds)
            ->where('active', true)
            ->with('entry')
            ->get();
        
        $this->entries = $eligibles->groupBy('category_id')
            ->map(function ($group) {
                return $group->map(function ($eligible) {
                    return $eligible->entry;
                })->filter()->values()->toArray();
            })
            ->toArray();
        
        // Initialize selections structure
        foreach ($this->categories as $category) {
            $this->selections[$category['id']] = [];
        }
        
        // Load user's existing votes (only non-deleted ones)
        $votes = NomineeVote::where('user_id', $this->user->id)
            ->with(['entry', 'category'])
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
        
        // Ensure selections are arrays
        foreach ($this->selections as $categoryId => $selections) {
            if (!is_array($selections)) {
                $this->selections[$categoryId] = [];
            }
        }
        
        // Load items (entries) - only load entries that are actually eligible for categories
        $entryIds = CategoryEligible::whereIn('category_id', $categoryIds)
            ->where('active', true)
            ->pluck('entry_id')
            ->unique()
            ->toArray();
        
        // Only load entries that are in category eligibles
        if (!empty($entryIds)) {
            // Load items and index by ID for O(1) lookups
            $itemsCollection = Entry::whereIn('id', $entryIds)->get();
            $this->items = [];
            foreach ($itemsCollection as $item) {
                $this->items[$item->id] = $item->toArray();
            }
        } else {
            $this->items = [];
        }
        
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
        
        // Update filtered items
        $this->updateFilteredItems();
        
        // Calculate initial progress
        $this->updateProgress();
        
        $this->loaded = true;
    }
    
    public function updatedSelectedGroup()
    {
        $this->updateVotingCategories();
        if (!empty($this->votingCategories)) {
            $this->selectedCategoryId = $this->votingCategories[0]['id'];
        } else {
            $this->selectedCategoryId = null;
        }
        $this->search = '';
        $this->updateSelectedCategory();

        // Clear cache when switching groups
        $this->categoryItemsCache = [];
        $this->updateFilteredItems();
        $this->updateProgress();
    }
    
    public function setSelectedGroup($group)
    {
        \Log::info('setSelectedGroup called', ['group' => $group]);
        $this->selectedGroup = $group;
        $this->updatedSelectedGroup();
    }
    
    public function updateVotingCategories()
    {
        $this->votingCategories = collect($this->categories)
            ->filter(function ($cat) {
                return $cat['type'] === $this->selectedGroup;
            })
            ->values()
            ->toArray();
    }
    
    public function selectCategory($categoryId)
    {
        \Log::info('selectCategory called', ['categoryId' => $categoryId]);
        $this->selectedCategoryId = $categoryId;
        $this->search = '';
        $this->updateSelectedCategory();
        
        // Clear cache for this category to force refresh if needed
        unset($this->categoryItemsCache[$categoryId]);
        $this->updateFilteredItems();
    }
    
    public function updateSelectedCategory()
    {
        if (!$this->selectedCategoryId) {
            $this->selectedCategory = null;
            return;
        }
        
        // Find the category from the categories array
        $this->selectedCategory = collect($this->categories)->firstWhere('id', $this->selectedCategoryId);
    }
    
    public function getCategoryEntriesProperty()
    {
        if (!$this->selectedCategoryId) {
            return [];
        }
        
        return $this->entries[$this->selectedCategoryId] ?? [];
    }
    
    public function getCategoryItemsProperty()
    {
        if (!$this->selectedCategoryId) {
            return [];
        }
        
        // Use cache if available
        if (isset($this->categoryItemsCache[$this->selectedCategoryId])) {
            return $this->categoryItemsCache[$this->selectedCategoryId];
        }
        
        $category = $this->selectedCategory;
        if (!$category) {
            return [];
        }
        
        $categoryEntries = $this->categoryEntries;
        $entryIds = collect($categoryEntries)->pluck('id')->toArray();
        
        // If no specific entries, return empty array
        if (empty($entryIds)) {
            $this->categoryItemsCache[$this->selectedCategoryId] = [];
            return [];
        }
        
        // Determine entry type from category name/type
        $entryType = $this->getEntryType($category);
        
        // Use array_intersect_key for faster filtering when items are keyed by ID
        $items = [];
        foreach ($entryIds as $entryId) {
            if (isset($this->items[$entryId])) {
                $item = $this->items[$entryId];
                // Filter by type
                if ($entryType === 'anime' && $item['type'] !== 'anime') {
                    continue;
                }
                if (($entryType === 'character' || $entryType === 'va') && $item['type'] !== 'char') {
                    continue;
                }
                $items[] = $item;
            }
        }
        
        // Cache the result
        $this->categoryItemsCache[$this->selectedCategoryId] = $items;
        
        return $items;
    }
    
    public function updateFilteredItems()
    {
        $items = $this->categoryItems;
        
        if (empty($this->search)) {
            // Sort: selected items first - use usort for better performance
            $selectedIds = collect($this->selections[$this->selectedCategoryId] ?? [])->pluck('id')->toArray();
            usort($items, function ($a, $b) use ($selectedIds) {
                $aSelected = in_array($a['id'], $selectedIds);
                $bSelected = in_array($b['id'], $selectedIds);
                if ($aSelected === $bSelected) {
                    return 0;
                }
                return $aSelected ? -1 : 1;
            });
            $this->filteredItems = $items;
        } else {
            $searchLower = strtolower($this->search);
            $this->filteredItems = array_filter($items, function ($item) use ($searchLower) {
                $name = strtolower($item['name'] ?? '');
                return str_contains($name, $searchLower);
            });
            // Re-index array
            $this->filteredItems = array_values($this->filteredItems);
        }
    }
    
    public function updatedSearch()
    {
        $this->updateFilteredItems();
    }
    
    public function getEntryType($category)
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
        
        $selections = $this->selections[$this->selectedCategoryId] ?? [];
        return collect($selections)->contains(function ($selection) use ($item) {
            return isset($selection['id']) && $selection['id'] == $item['id'];
        });
    }
    
    public function canVoteMore()
    {
        if (!$this->selectedCategoryId) {
            return false;
        }
        
        $count = count($this->selections[$this->selectedCategoryId] ?? []);
        return $count < 5;
    }
    
    public function toggleVote($itemId)
    {
        if (!$this->selectedCategoryId) {
            return;
        }
        
        $item = collect($this->items)->firstWhere('id', $itemId);
        if (!$item) {
            return;
        }
        
        $isSelected = $this->isItemSelected($item);
        
        if ($isSelected) {
            $this->removeVote($itemId);
        } else {
            $this->addVote($itemId);
        }
    }
    
    public function addVote($itemId)
    {
        if (!$this->canVoteMore()) {
            session()->flash('error', 'You cannot vote for any more entries in this category.');
            return;
        }
        
        $item = collect($this->items)->firstWhere('id', $itemId);
        if (!$item) {
            return;
        }
        
        if ($this->isItemSelected($item)) {
            return;
        }
        
        // Find or create the category eligible entry
        $categoryEligible = CategoryEligible::where('category_id', $this->selectedCategoryId)
            ->where('entry_id', $itemId)
            ->first();
        
        if (!$categoryEligible) {
            // Create the category eligible if it doesn't exist
            $categoryEligible = CategoryEligible::create([
                'category_id' => $this->selectedCategoryId,
                'entry_id' => $itemId,
                'active' => true,
            ]);
        }
        
        // Check if vote already exists
        $existingVote = NomineeVote::where('user_id', $this->user->id)
            ->where('category_id', $this->selectedCategoryId)
            ->where('entry_id', $itemId)
            ->first();
        
        if ($existingVote) {
            return; // Vote already exists
        }
        
        // Create the vote
        NomineeVote::create([
            'user_id' => $this->user->id,
            'category_id' => $this->selectedCategoryId,
            'cat_entry_id' => $categoryEligible->id,
            'entry_id' => $itemId,
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
    
    public function removeVote($itemId)
    {
        if (!$this->selectedCategoryId) {
            return;
        }
        
        // Delete the vote
        NomineeVote::where('user_id', $this->user->id)
            ->where('category_id', $this->selectedCategoryId)
            ->where('entry_id', $itemId)
            ->delete();
        
        // Update local selections
        $selections = $this->selections[$this->selectedCategoryId] ?? [];
        $this->selections[$this->selectedCategoryId] = collect($selections)
            ->reject(function ($selection) use ($itemId) {
                return $selection['id'] == $itemId;
            })
            ->values()
            ->toArray();
        
        $this->updateFilteredItems();
        $this->updateProgress();
        $this->dispatch('vote-removed');
    }
    
    public function updateProgress()
    {
        // Count how many categories across all groups the user has voted in
        $this->progress = collect($this->selections)
            ->filter(function ($selections, $categoryId) {
                return !empty($selections) && count($selections) > 0;
            })
            ->count();
    }
    
    public function getVotedCategoriesProperty()
    {
        $voted = [];
        foreach ($this->selections as $categoryId => $selections) {
            $voted[$categoryId] = !empty($selections);
        }
        return $voted;
    }
    
    public function render()
    {
        return view('livewire.nomination-voting');
    }
}

