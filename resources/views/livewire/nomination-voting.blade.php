<div class="voting-page-content has-background-anti-spotify">
        @if($loaded)
            <div class="voting-page-content-container" 
            x-data="{selectedGroup: 'genre',
                selectedCategoryId: '',
                categories: {{ Js::from($this->categories) }},
                selections: {{ Js::from($this->selections) }},
                addSelection(eligible) {
                    if(!this.selections[eligible.category_id]) {
                        this.selections[eligible.category_id] = {};
                    }
                    this.selections[eligible.category_id][eligible.id] = eligible;
                },
                removeSelection(eligible) {
                    delete this.selections[eligible.category_id]?.[eligible.id];
                    if(this.selections[eligible.category_id] && Object.keys(selections[eligible.category_id]).length == 0) {
                        delete this.selections[eligible.category_id];
                    }
                }
            }">
                <div class="has-text-light">
                    <!-- Category Group Tabs -->
                    <div class="container mt-4">
                        <div class="tabs is-boxed">
                            <ul x-data="{ groups: {{ Js::from($this->groups) }} }">
                                <template x-for="group in groups" :key="group.slug">
                                    <li :class="selectedGroup == group.slug? 'is-active' : ''">
                                        <a href="#" 
                                            x-text="group.text"
                                            x-on:click="selectedGroup = group.slug">
                                    </li>
                                </template>                                
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Category Tabs -->
                    <div class="tab-container container"
                        x-init="selectedCategoryId = categories[selectedGroup][0].id;
                            $watch('selectedGroup', group => selectedCategoryId = categories[group]?.[0]?.id ?? null);
                        "
                    >
                        <div class="mobile-select has-background-dark">
                            <progress class="progress is-tiny is-primary mb-3" 
                                x-data="{ 
                                    get currentProgress() {
                                        return Object.values(selections ?? {}).length;
                                    },
                                    get maxProgress() {
                                        return Object.values(categories).reduce((carry, categoryGroup) => 
                                            carry + categoryGroup.length, 0);
                                    }
                                }"
                                x-bind:value="currentProgress" 
                                x-bind:max="maxProgress"
                            >
                            </progress>
                            <div class="mobile-select-container">
                                <template x-for="category in categories[selectedGroup]" :key="category.id">
                                    <a href="#"
                                        class="sidebar-item"
                                        :class="selectedCategoryId == category.id? 'current-tab' : ''"
                                        x-on:click="selectedCategoryId = category.id"
                                        style="cursor: pointer;"
                                        x-text="category.name"
                                    >
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container mt-4" 
                    x-data="{ eligibles: [],
                            loaded: false,
                            async fetchEligibles(catId) {
                                if(!catId) {
                                    this.eligibles = [];
                                    return;
                                }
                                const data = await $wire.fetchEligibles(catId);
                                const cacheKey = 'eligibles-'+catId;
                                {{-- sessionStorage.setItem(cacheKey, JSON.stringify(data)); --}}
                                return data;
                            },
                            async loadEligibles(catId) {
                                this.loaded = false;
                                const cacheKey = 'eligibles-'+catId;
                                let data = [];
                                if(sessionStorage.getItem(cacheKey)) {
                                    data = JSON.parse(sessionStorage.getItem(cacheKey));
                                } else {
                                    data = await this.fetchEligibles(catId);
                                }
                                this.loaded = true;
                                return data;
                            },
                            async removeVote(eligible) {
                                {{-- ! Not sure how to have handled this --}}
                                try{
                                    response = await $wire.deleteVote(eligible.category_id, eligible.id, eligible.entry.id)
                                    if (response && response.original.success) {
                                        removeSelection(eligible);
                                    } else {
                                        this.error = response.original.error;
                                    }
                                } catch (error) {
                                    this.error = error.message;
                                }
                            },
                            async addVote(eligible) {
                                {{-- Ignore if at vote limit --}}
                                if(Object.keys(selections[eligible.category_id] ?? {}).length >= 5)
                                    return;
                                {{-- ! Not sure how to have handled this --}}
                                try{
                                    response = await $wire.createVote(eligible.category_id, eligible.id, eligible.entry.id)
                                    if (response && response.original.success) {
                                        addSelection(eligible);
                                    } else {
                                        this.error = response.original.error;
                                    }
                                } catch (error) {
                                    this.error = error.message;
                                }
                            }
                        }"
                    x-init="eligibles = await loadEligibles(selectedCategoryId);
                        $watch('selectedCategoryId', async (catId) => {
                            eligibles = [];
                            eligibles = await loadEligibles(catId);
                            
                        })"
                        >
                    <template x-if="selectedCategoryId">
                        <div class="columns">
                            <div class="column is-10-widescreen is-12"
                                x-data="{search: '',
                                    selectedEligibles: [],
                                    filteredEligibles: [],
                                    filterEligibles() {
                                        if(eligibles.length == 0) {
                                            this.selectedEligibles = [];
                                            this.filteredEligibles = [];
                                        }
                                        
                                        this.selectedEligibles = Object.values(selections[selectedCategoryId]??{});
                                        
                                        const searchKey = this.search?.toLowerCase();
                                        const searchedEligibles = [];
                                        eligibles.forEach(eligible => {
                                            if (searchedEligibles.length >= 25) {
                                                return;
                                            }
                                            if(selections[selectedCategoryId]?.[eligible.id]) {
                                                
                                            } else {
                                                if(searchKey) {
                                                    if(eligible.entry.name?.toLowerCase().includes(searchKey)) {
                                                      searchedEligibles.push(eligible);                                                        
                                                    }
                                                } else {
                                                    searchedEligibles.push(eligible);                                                        
                                                }
                                            }
                                        });
                                        this.filteredEligibles =  searchedEligibles;
                                    }
                                }"
                                x-init="filterEligibles();
                                    $watch('search', search => {filterEligibles(); });
                                    $watch('eligibles', eligibles => {filterEligibles(); });
                                    $watch('selections', selections => {filterEligibles(); });
                                "
                            >
                                <!-- Search Bar -->
                                <div class="field has-addons mb-4">
                                    <p class="control has-icons-left is-expanded">
                                        <input class="input is-primary" 
                                               type="text" 
                                               x-model="search"
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
                                        <span x-text="((selectedEligibles.length+filteredEligibles.length) + ' entries are displayed out of ' + eligibles.length)"></span>
                                    We strongly recommend using the search bar to look for your shows.
                                </small>
                                
                                <!-- Entry Grid -->
                                <div class="show-picker-entries" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
                                    {{-- <template x-if="filteredEligibles.length>0"> --}}
                                        <template x-for="selectedEligible in selectedEligibles">
                                        <div class="box entry-card" 
                                             style="cursor: pointer; position: relative; padding: 0; overflow: hidden;
                                                background: #00d1b2;"
                                             :key="selectedEligible.id"
                                             x-on:click="await removeVote(selectedEligible)"
                                        >
                                            <template x-if="selectedEligible.entry.image">
                                                <figure class="image" style="margin: 0;">
                                                    <img x-bind:src="'/storage/'+selectedEligible.entry.image"
                                                         alt="eligible.entry.name"
                                                         style="width: 100%; height: 200px; object-fit: cover;">
                                                </figure>
                                            </template>
                                            <div class="p-3" style="background: rgba(0,0,0,0.7);">
                                                <p class="has-text-white" 
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="selectedEligible.entry.name"
                                                >
                                                </p>
                                                    <span class="tag is-success mt-2">Selected</span>
                                            </div>
                                        </div>
                                        </template>
                                        <template x-for="filteredEligible in filteredEligibles">
                                        <div class="box entry-card" 
                                             style="cursor: pointer; position: relative; padding: 0; overflow: hidden; 
                                                background: #2d3853;"
                                             :key="filteredEligible.id"
                                             x-on:click="await addVote(filteredEligible)"
                                        >
                                            <template x-if="filteredEligible.entry.image">
                                                <figure class="image" style="margin: 0;">
                                                    <img x-bind:src="'/storage/'+filteredEligible.entry.image"
                                                         x-bind:alt="filteredEligible.entry.name"
                                                         style="width: 100%; height: 200px; object-fit: cover;">
                                                </figure>
                                            </template>
                                            <div class="p-3" style="background: rgba(0,0,0,0.7);">
                                                <p class="has-text-white" 
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="filteredEligible.entry.name"
                                                >
                                                </p>
                                                <p class="has-text-white" 
                                                    x-if="filteredEligible.entry.parent"
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="'(' + filteredEligible.entry.parent.name + ')'"
                                                >
                                                </p>
                                                <p class="has-text-white" 
                                                    x-if="filteredEligible.entry.parent.grandparents"
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="'from ' + filteredEligible.entry.parent.grandparents.name"
                                                >
                                                </p>
                                            </div>
                                        </div>
                                        </template>
                                    {{-- </template> --}}
                                    <template x-if="filteredEligibles.length==0">
                                        <div class="has-text-light" 
                                            x-text="search? 
                                            'No results found.' : 
                                            'No entries available for this category.'
                                            ">
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Selections Sidebar -->
                            <div class="column is-2-widescreen is-12 selection-column">
                                <div class="panel is-primary has-background-dark">
                                    <p class="panel-heading">My Selections</p>
                                    <div class="panel-block">
                                        <p class="has-text-light" 
                                            x-text="(Object.keys(selections[selectedCategoryId] ?? {}).length)+'/ 5 selected'">
                                        </p>
                                    </div>
                                    <template x-if="Object.keys(selections[selectedCategoryId] ?? {}).length > 0">
                                        <template x-for="selection in Object.values(selections[selectedCategoryId])" :key="selection.id">
                                            <a class="panel-block has-text-light" 
                                               x-on:click="removeVote(selection)"
                                               style="cursor: pointer;">
                                                <span class="delete"></span>
                                                <span class="ml-2" x-text="selection.entry.name"></span>
                                            </a>
                                        </template>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selectedCategoryId">
                        <div class="has-text-light has-text-centered">
                            <template x-if="categories[selectedGroup]?.length == 0">
                                <p>No categories available for voting at this time.</p>
                            </template>
                            <template x-if="categories[selectedGroup]?.length > 0">
                                <p>Please select a category to begin voting.</p>
                            </template>
                        </div>
                    </template>
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

        .box.entry-card {
            display: flex;
            flex-direction: column;
        }

        .box.entry-card div {
            flex: 1 0 auto;
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
</div>