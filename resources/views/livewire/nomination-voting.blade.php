<div class="voting-page-content has-background-anti-spotify">
        @if($loaded)
            <div class="voting-page-content-container">
                <div class="has-text-light">
                    <!-- Category Group Tabs -->
                    <div class="container mt-4">
                        <div class="tabs is-boxed">
                            <ul>
                                <li wire:key="group-main" class="{{ $selectedGroup === 'main' ? 'is-active' : '' }}">
                                    <a href="#" wire:click="setSelectedGroup('main')">Main Awards</a>
                                </li>
                                <li wire:key="group-genre" class="{{ $selectedGroup === 'genre' ? 'is-active' : '' }}">
                                    <a href="#" wire:click="setSelectedGroup('genre')">Genre Awards</a>
                                </li>
                                <li wire:key="group-production" class="{{ $selectedGroup === 'production' ? 'is-active' : '' }}">
                                    <a href="#" wire:click="setSelectedGroup('production')">Production Awards</a>
                                </li>
                                <li wire:key="group-character" class="{{ $selectedGroup === 'character' ? 'is-active' : '' }}">
                                    <a href="#" wire:click="setSelectedGroup('character')">Character Awards</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Category Tabs -->
                    <div class="tab-container container">
                        <div class="mobile-select has-background-dark">
                            <progress class="progress is-tiny is-primary mb-3" 
                                value="{{ $progress }}" 
                                max="{{ count($categories) }}">
                            </progress>
                            <div class="mobile-select-container">
                                @foreach($votingCategories as $category)
                                    <a href="#" 
                                       class="sidebar-item {{ $selectedCategoryId == $category['id'] ? 'current-tab' : '' }} {{ ($votedCategories[$category['id']] ?? false) ? 'voted' : '' }}"
                                       wire:click="selectCategory({{ $category['id'] }})"
                                       wire:key="category-{{ $category['id'] }}"
                                       style="cursor: pointer;">
                                        {{ $category['name'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="container mt-4">
                    @if($selectedCategory)
                        <div class="columns">
                            <div class="column is-10-widescreen is-12">
                                <!-- Search Bar -->
                                <div class="field has-addons mb-4">
                                    <p class="control has-icons-left is-expanded">
                                        <input class="input is-primary" 
                                               type="text" 
                                               wire:model.live.debounce.300ms="search"
                                               placeholder="Search by title...">
                                        <span class="icon is-left has-text-platinum">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                            </svg>
                                        </span>
                                    </p>
                                </div>
                                
                                <small class="has-text-light mb-4" style="display: block;">
                                    You may vote up to 5 times per category. 
                                    @if(!empty($filteredItems))
                                        {{ count($filteredItems) }} entries are displayed.
                                    @endif
                                    We strongly recommend using the search bar to look for your shows.
                                </small>
                                
                                <!-- Entry Grid -->
                                <div class="show-picker-entries" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
                                    @forelse($filteredItems as $item)
                                        <div class="box entry-card" 
                                             style="cursor: pointer; position: relative; padding: 0; overflow: hidden; background: {{ $this->isItemSelected($item) ? '#00d1b2' : '#2d3853' }};"
                                             wire:click="toggleVote({{ $item['id'] }})">
                                            @if(!empty($item['image']))
                                                <figure class="image" style="margin: 0;">
                                                    <img src="{{ asset('storage/' . ltrim($item['image'], '/')) }}" 
                                                         alt="{{ $item['name'] }}"
                                                         style="width: 100%; height: 200px; object-fit: cover;">
                                                </figure>
                                            @endif
                                            <div class="p-3" style="background: rgba(0,0,0,0.7);">
                                                <p class="has-text-white" style="font-size: 0.9rem; word-wrap: break-word;">
                                                    {{ $item['name'] }}
                                                </p>
                                                @if($this->isItemSelected($item))
                                                    <span class="tag is-success mt-2">Selected</span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="has-text-light">
                                            {{ $search ? 'No results found.' : 'No entries available for this category.' }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            
                            <!-- Selections Sidebar -->
                            <div class="column is-2-widescreen is-12 selection-column">
                                <div class="panel">
                                    <p class="panel-heading">My Selections</p>
                                    <div class="panel-block">
                                        <p class="has-text-dark">
                                            {{ count($selections[$selectedCategoryId] ?? []) }} / 5 selected
                                        </p>
                                    </div>
                                    @if(!empty($selections[$selectedCategoryId]))
                                        @foreach($selections[$selectedCategoryId] as $selection)
                                            <a class="panel-block has-text-dark" 
                                               wire:click="removeVote({{ $selection['id'] }})"
                                               style="cursor: pointer;">
                                                <span class="delete"></span>
                                                {{ $selection['name'] }}
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="has-text-light has-text-centered">
                            @if(empty($votingCategories))
                                <p>No categories available for voting at this time.</p>
                            @else
                                <p>Please select a category to begin voting.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @else
            <section class="hero">
                <div class="hero-body">
                    <div class="columns is-centered pt-5">
                        <div class="column is-5-tablet is-4-desktop is-3-widescreen pt-5">
                            <div class="loading-text has-text-light has-text-centered">
                                Please wait while your selections are being initialized. Thank you for your patience.
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
        <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow-x: hidden;
        }
        
        .has-background-anti-spotify {
            background: linear-gradient(360deg, rgba(107, 156, 232, 0.35) 0%, rgba(45, 56, 83, 0) 76.46%), #1B1E25;
            height: 100vh;
            overflow-y: auto;
        }
        
        .mobile-select {
            position: static;
            top: initial;
            left: initial;
            height: 50px;
            padding: 0;
            padding-left: 100px;
            padding-right: 100px;
            width: 100%;
            padding-top: 5px;
            overflow-x: auto;
            overflow-y: hidden;
            z-index: 9998;
            white-space: nowrap;
        }
        
        .mobile-select:before {
            content: '';
            display: block;
            position: fixed;
            height: 60px;
            width: 100px;
            top: 52px;
            left: 0;
            background-image: linear-gradient(to right, #1B1E25, transparent 70%);
            pointer-events: none;
        }
        
        .mobile-select:after {
            content: '';
            display: block;
            position: fixed;
            height: 60px;
            width: 100px;
            top: 52px;
            right: 0;
            background-image: linear-gradient(to left, #1B1E25, transparent 70%);
            pointer-events: none;
        }
        
        .progress.is-tiny {
            height: 3px;
            width: 100%;
            position: fixed;
            left: 0;
            top: 0px;
            border-radius: 0 !important;
            z-index: 9999;
        }
        
        .sidebar-item {
            display: inline-block;
            padding: 5px 15px;
            margin: 5px 5px;
            font-size: 12px;
            color: #fff;
            background: rgba(87,150,255, 0);
            border: #7EAEFF 1px solid;
            border-radius: 30px;
            transition: background-color 0.2s;
        }
        
        .sidebar-item:hover {
            background: rgba(0, 209, 178, 0.3);
        }
        
        .sidebar-item.voted {
            border: #00D1B2 1px solid;
        }
        
        .sidebar-item.current-tab {
            background: #00D1B2;
            border: #00D1B2 1px solid;
        }
        
        .selection-column {
            margin-top: 2rem;
        }
        
        .entry-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .entry-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        @media (min-width: 1216px) {
            .mobile-select {
                position: fixed;
                top: 8px;
                left: 0px;
                height: calc(100vh - 8px);
                padding: 0;
                padding-top: 20px;
                width: 220px;
                overflow-x: hidden;
                overflow-y: auto;
                z-index: 9999;
                margin-top: 0;
            }
            
            .mobile-select:before,
            .mobile-select:after {
                display: none;
            }
            
            .progress.is-tiny {
                height: 8px;
            }
            
            .sidebar-item {
                display: block;
                padding: 10px 20px;
                margin: 0;
                font-size: 12px;
                color: #B7B7B7;
                background: rgba(87,150,255, 0);
                border: 0 transparent solid !important;
                border-radius: 0;
                width: 100%;
            }
            
            .sidebar-item:hover {
                background: rgba(87,150,255, 0.15);
            }
            
            .sidebar-item.current-tab {
                background: rgba(87,150,255, 0.3);
            }
            
            .sidebar-item.voted {
                color: #00D1B2;
            }
        }
        
        @media (max-width: 1215.999px) {
            .selection-column {
                position: fixed;
                top: 9999px;
                left: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                width: 100%;
                height: 100%;
                background: transparent;
            }
        }
    </style>

