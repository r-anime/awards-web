<div class="voting-page-content has-background-anti-spotify">
        @if($loaded)
            <div class="voting-page-content-container" 
            x-data="{selectedGroup: 'genre',
                selectedCategory: null,
                groups: {{ Js::from($this->groups) }},
                categories: {{ Js::from($this->categories) }},
                entries: {},
                eligibles: {},
                loadedEntries: {
                    'anime' : false,
                    'theme' : false,
                    'char'  : false,
                    'va'    : false
                },
                loadedEligibles: {}, {{-- Booleans with Category IDs as keys --}}
                fetchEntries(type) {
                    {{-- console.time(type + ' fetch'); --}}
                    $wire.fetchEntriesByType(type).then(data => {
                        data.forEach(entry => this.entries[entry.id]= entry);
                        this.loadedEntries[type] = true;
                        {{-- console.timeEnd(type + ' fetch'); --}}
                    });
                },
                fetchEligibles(categoryId) {
                    this.loadedEligibles[categoryId] = false;
                    $wire.fetchEligibles(categoryId).then(data => {
                        this.eligibles[categoryId] = data;
                        this.loadedEligibles[categoryId] = true;
                    });

                },
                init() {
                    this.fetchEntries('anime');
                    this.fetchEntries('theme');
                    this.fetchEntries('char');
                    this.fetchEntries('va');
                    this.groups.forEach(group => {
                        this.categories[group.slug].forEach(category => {
                            this.fetchEligibles(category.id);
                        });
                    });
                }
            }">
            {{-- Separating selections from static data --}}
            <div
                x-data="{
                    selections: {},
                    init() {
                        const data = {{ Js::from($this->selections) }};
                        Object.keys(data).forEach(categoryId => {
                            Object.keys(data[categoryId]).forEach(eligibleId => {
                                this.initialAddSelection(data[categoryId][eligibleId]);
                            });
                        });
                    },
                    initialAddSelection(eligible) {
                        if(!this.selections[eligible.category_id]) {
                            this.selections[eligible.category_id] = {};
                        }
                        this.selections[eligible.category_id][eligible.cat_entry_id] = eligible;
                    },
                    addSelection(eligible) {
                        if(!this.selections[eligible.category_id]) {
                            this.selections[eligible.category_id] = {};
                        }
                        this.selections[eligible.category_id][eligible.cat_entry_id] = eligible;
                    },
                    removeSelection(eligible) {
                        if (!this.selections) {
                            return;
                        }
                        if (!this.selections[eligible.category_id]) {
                            return;
                        }
                        console.log('eligible:', eligible);
                        console.log('this.selections[eligible.category_id]:', this.selections[eligible.category_id]);
                        delete this.selections[eligible.category_id][eligible.cat_entry_id];
                        if(this.selections[eligible.category_id] && Object.keys(this.selections[eligible.category_id]).length == 0) {
                            delete this.selections[eligible.category_id];
                        }
                    },
                }"
            >
                <div class="has-text-light">
                    <!-- Category Group Tabs -->
                    <div class="container mt-4">
                        <div class="tabs is-boxed">
                            <ul>
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
                        x-init="selectedCategory = categories[selectedGroup][0];
                            $watch('selectedGroup', group => selectedCategory = categories[group]?.[0]?? null);
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
                                        :class="selectedCategory.id == category.id? 'current-tab' : ''"
                                        x-on:click="selectedCategory = category"
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
                    x-data="{ 
                            get loaded() {return loadedEntries[selectedGroup]},
                            async removeVote(eligible) {
                                {{-- ! Currently causing frontend issues --}}
                                try{
                                    response = await $wire.deleteVote(eligible.category_id, eligible.id, eligible.entry_id)
                                    if (response && response.original.success) {
                                        removeSelection(eligible);
                                    } else {
                                        this.error = response.original.error;
                                    }
                                } catch (error) {
                                    this.error = error.message;
                                    console.error(error);
                                }
                            },
                            async addVote(eligible) {
                                {{-- Ignore if at vote limit --}}
                                if(Object.keys(selections[eligible.category_id] ?? {}).length >= 5)
                                    return;
                                {{-- ! Currently causing frontend issues --}}
                                try{
                                    response = await $wire.createVote(eligible.category_id, eligible.id, eligible.entry_id)
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
                    x-init=""
                        >
                    <template x-if="selectedCategory && loadedEntries[selectedCategory.entry_type]">
                        <div class="columns">
                            <div class="column is-10-widescreen is-12"
                                x-data="{search: '',
                                    get categoryEligibles() {return eligibles[selectedCategory.id]},
                                    selectedEligibles: [],
                                    filteredEligibles: [],
                                    loadedEligibles: false,
                                    filterEligibles() {
                                        this.loadedEligibles = false;
                                        if(this.categoryEligibles.length == 0) {
                                            this.selectedEligibles = [];
                                            this.filteredEligibles = [];
                                        }
                                        
                                        this.selectedEligibles = Object.values(selections[selectedCategory.id]??{});

                                        const searchKey = this.search?.toLowerCase();
                                        const searchedEligibles = [];

                                        const limit = {{ Js::from($this->displayLimit) }};

                                        // console.log('selections[selectedCategory.id]:', selections[selectedCategory.id]);

                                        for(let i=0; i<this.categoryEligibles.length; i++) {
                                            if (searchedEligibles.length>=limit+this.selectedEligibles.length){
                                                break;
                                            }
                                            const eligible = this.categoryEligibles[i];
                                            // console.log('eligible:', eligible);
                                            if(selections[selectedCategory.id]?.[eligible.id]) {
                                                continue;
                                            }
                                            if(!searchKey) {
                                                searchedEligibles.push(eligible);    
                                                continue;
                                            } 

                                            if(entries[eligible.entry_id].searchable_string?.toLowerCase().includes(searchKey)) {
                                                searchedEligibles.push(eligible);
                                                continue;
                                            }
                                            if(entries[eligible.entry_id].parent_id && entries[entries[eligible.entry_id].parent_id]){
                                                var parent = entries[eligible.entry_id].parent_id;
                                                console.log(parent);
                                                if(entries[parent].searchable_string?.toLowerCase().includes(searchKey)) {
                                                    searchedEligibles.push(eligible);
                                                    continue;
                                                }
                                            }
                                        }

                                        this.filteredEligibles =  searchedEligibles;
                                        this.loadedEligibles = true;
                                    }
                                }"
                                x-init="filterEligibles();
                                    $watch('selectedCategory', category => {filterEligibles(); });
                                    $watch('search', search => {filterEligibles(); });
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
                                        <span x-text="((selectedEligibles.length+filteredEligibles.length) + ' entries are displayed out of ' + categoryEligibles.length)"></span>
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
                                             x-data="{
                                                get selectedEntry() {return entries[selectedEligible.entry_id];},
                                                get parent() {return this.selectedEntry.parent_id? entries[this.selectedEntry.parent_id] : null},
                                                get grandparent() {return this.parent?.parent_id? entries[this.parent.parent_id] : null},
                                            }"
                                             x-on:click="await removeVote(selectedEligible)"
                                        >
                                            <template x-if="selectedEntry.image">
                                                <figure class="image" style="margin: 0;">
                                                    <img x-bind:src="'/storage/'+selectedEntry.image" 
                                                    {{-- <img src="/storage/entry/anilist-174596.jpg" --}}
                                                         x-bind:alt="selectedEntry.name"
                                                         style="width: 100%; height: 200px; object-fit: cover;">
                                                </figure>
                                            </template>
                                            <template x-if="!selectedEntry.image && parent">
                                                <figure class="image" style="margin: 0;">
                                                    <img x-bind:src="'/storage/'+parent?.image"
                                                         x-bind:alt="parent?.name"
                                                         style="width: 100%; height: 200px; object-fit: cover;">
                                                </figure>
                                            </template>
                                            <div class="p-3" style="background: rgba(0,0,0,0.7);">
                                                <p class="has-text-white" 
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="selectedEntry.name"
                                                >
                                                </p>
                                                <p class="has-text-white" 
                                                    x-show="parent"
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="'(' + parent?.name + ')'"
                                                >
                                                </p>
                                                <p class="has-text-white" 
                                                    x-show="selectedEntry.theme_version"
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="selectedEntry.theme_version"
                                                >
                                                </p>
                                                <p class="has-text-white" 
                                                    x-show="grandparent"
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="'from ' + grandparent?.name"
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
                                             x-data="{ 
                                                get filteredEntry() {return entries[filteredEligible.entry_id]},
                                                get parent() {return this.filteredEntry.parent_id? entries[this.filteredEntry.parent_id] : null},
                                                get grandparent() {return this.parent?.parent_id? entries[this.parent.parent_id] : null},
                                             }"
                                             x-on:click="await addVote(filteredEligible)"
                                        >
                                            <template x-if="filteredEntry.image">
                                                <figure class="image" style="margin: 0;">
                                                    <img x-bind:src="'/storage/'+filteredEntry.image"
                                                         x-bind:alt="filteredEntry.name"
                                                         style="width: 100%; height: 200px; object-fit: cover;">
                                                </figure>
                                            </template>
                                            <template x-if="!filteredEntry.image && parent">
                                                <figure class="image" style="margin: 0;">
                                                    <img x-bind:src="'/storage/'+parent?.image"
                                                         x-bind:alt="parent?.name"
                                                         style="width: 100%; height: 200px; object-fit: cover;">
                                                </figure>
                                            </template>
                                            <div class="p-3" style="background: rgba(0,0,0,0.7);">
                                                <p class="has-text-white" 
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="filteredEntry.name"
                                                >
                                                </p>
                                                <p class="has-text-white" 
                                                    x-show="parent"
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="'(' + parent?.name + ')'"
                                                >
                                                <p class="has-text-white" 
                                                    x-show="filteredEntry.theme_version"
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="filteredEntry.theme_version"
                                                >
                                                </p>
                                                <p class="has-text-white" 
                                                    x-show="grandparent"
                                                    style="font-size: 0.9rem; word-wrap: break-word;"
                                                    x-text="'from ' + grandparent?.name"
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
                                            x-text="(Object.keys(selections[selectedCategory?.id] ?? {}).length)+'/ 5 selected'">
                                        </p>
                                    </div>
                                    <template x-if="Object.keys(selections[selectedCategory?.id] ?? {}).length > 0">
                                        <template x-for="selection in Object.values(selections[selectedCategory.id]??{})" :key="selection.id">
                                            <a class="panel-block has-text-light" 
                                               x-on:click="removeVote(selection)"
                                               style="cursor: pointer;">
                                                <span class="delete"></span>
                                                <span class="ml-2" x-text="entries[selection.entry_id].name"></span>
                                            </a>
                                        </template>
                                    </template>
                                    <div class="panel-block">
                                        <button class="button is-primary is-fullwidth"
                                                x-data="{
                                                    formatSelectionsForReddit() {
                                                        let formatted = '';
                                                        const allCategories = [];
                                                        
                                                        // Collect all categories from all groups
                                                        groups.forEach(group => {
                                                            if (categories[group.slug]) {
                                                                categories[group.slug].forEach(category => {
                                                                    allCategories.push(category);
                                                                });
                                                            }
                                                        });
                                                        
                                                        // Sort categories by order if available, otherwise keep original order
                                                        allCategories.sort((a, b) => (a.order || 0) - (b.order || 0));
                                                        
                                                        // Format each category with selections
                                                        allCategories.forEach(category => {
                                                            const categorySelections = selections[category.id] || {};
                                                            const selectionValues = Object.values(categorySelections);
                                                            
                                                            if (selectionValues.length > 0) {
                                                                formatted += `*${category.name}:*\n`;
                                                                selectionValues.forEach(selection => {
                                                                    const entry = entries[selection.entry_id];
                                                                    if (entry) {
                                                                        formatted += ` - ${entry.name}\n`;
                                                                    }
                                                                });
                                                                formatted += '\n';
                                                            }
                                                        });
                                                        
                                                        return formatted.trim();
                                                    },
                                                    async shareSelections() {
                                                        const redditText = this.formatSelectionsForReddit();
                                                        
                                                        if (!redditText) {
                                                            alert('No selections to share yet!');
                                                            return;
                                                        }
                                                        
                                                        if (navigator.share) {
                                                            try {
                                                                await navigator.share({
                                                                    title: 'My Award Nominations',
                                                                    text: redditText,
                                                                });
                                                            } catch (err) {
                                                                if (err.name !== 'AbortError') {
                                                                    this.copyToClipboard(redditText);
                                                                }
                                                            }
                                                        } else {
                                                            this.copyToClipboard(redditText);
                                                        }
                                                    },
                                                    copyToClipboard(text) {
                                                        navigator.clipboard.writeText(text).then(() => {
                                                            alert('Your votes have been copied to your clipboard!');
                                                        }).catch(() => {
                                                            alert('Failed to copy your votes.');
                                                        });
                                                    }
                                                }"
                                                x-on:click="shareSelections()">
                                            <span class="icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M13.5 1a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.499 2.499 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5zm-8.5 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm11 5.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/>
                                                </svg>
                                            </span>
                                            <span>Share</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mobile Selections FAB and Bottom Sheet -->
                            <div class="mobile-selections-container"
                                 x-data="{ 
                                     showSelections: false,
                                     get selectionCount() {
                                         return Object.keys(selections[selectedCategory?.id] ?? {}).length;
                                     }
                                 }"
                                 x-show="selectedCategory"
                            >
                                <!-- Floating Action Button -->
                                <button class="mobile-selections-fab"
                                        x-on:click="showSelections = !showSelections"
                                        x-bind:class="{ 'has-selections': selectionCount > 0 }"
                                        x-bind:title="'My Selections (' + selectionCount + '/5)'">
                                    <span x-text="selectionCount + '/5'"></span>
                                </button>
                                
                                <!-- Bottom Sheet Overlay -->
                                <div class="mobile-selections-overlay"
                                     x-show="showSelections"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     x-on:click="showSelections = false"
                                ></div>
                                
                                <!-- Bottom Sheet Panel -->
                                <div class="mobile-selections-sheet"
                                     x-show="showSelections"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="translate-y-full"
                                     x-transition:enter-end="translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="translate-y-0"
                                     x-transition:leave-end="translate-y-full"
                                     x-on:click.stop
                                >
                                    <div class="mobile-selections-header">
                                        <h3 class="has-text-light">My Selections</h3>
                                        <button class="mobile-selections-close" x-on:click="showSelections = false">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="mobile-selections-content">
                                        <div class="mobile-selections-count">
                                            <p class="has-text-light" 
                                               x-text="(Object.keys(selections[selectedCategory?.id] ?? {}).length)+'/ 5 selected'">
                                            </p>
                                        </div>
                                        <template x-if="Object.keys(selections[selectedCategory?.id] ?? {}).length > 0">
                                            <div class="mobile-selections-list">
                                                <template x-for="selection in Object.values(selections[selectedCategory.id]??{})" :key="selection.id">
                                                    <div class="mobile-selection-item" 
                                                         x-on:click="removeVote(selection)">
                                                        <span class="mobile-selection-name" x-text="entries[selection.entry_id].name"></span>
                                                        <button class="mobile-selection-remove">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="Object.keys(selections[selectedCategory?.id] ?? {}).length === 0">
                                            <div class="mobile-selections-empty">
                                                <p class="has-text-light">No selections yet</p>
                                            </div>
                                        </template>
                                        <div class="mobile-selections-share">
                                            <button class="button is-primary is-fullwidth"
                                                    x-data="{
                                                        formatSelectionsForReddit() {
                                                            let formatted = '';
                                                            const allCategories = [];
                                                            
                                                            // Collect all categories from all groups
                                                            groups.forEach(group => {
                                                                if (categories[group.slug]) {
                                                                    categories[group.slug].forEach(category => {
                                                                        allCategories.push(category);
                                                                    });
                                                                }
                                                            });
                                                            
                                                            // Sort categories by order if available, otherwise keep original order
                                                            allCategories.sort((a, b) => (a.order || 0) - (b.order || 0));
                                                            
                                                            // Format each category with selections
                                                            allCategories.forEach(category => {
                                                                const categorySelections = selections[category.id] || {};
                                                                const selectionValues = Object.values(categorySelections);
                                                                
                                                                if (selectionValues.length > 0) {
                                                                    formatted += `*${category.name}:*\n`;
                                                                    selectionValues.forEach(selection => {
                                                                        const entry = entries[selection.entry_id];
                                                                        if (entry) {
                                                                            formatted += ` - ${entry.name}\n`;
                                                                        }
                                                                    });
                                                                    formatted += '\n';
                                                                }
                                                            });
                                                            
                                                            return formatted.trim();
                                                        },
                                                        async shareSelections() {
                                                            const redditText = this.formatSelectionsForReddit();
                                                            
                                                            if (!redditText) {
                                                                alert('No selections to share yet!');
                                                                return;
                                                            }
                                                            
                                                            if (navigator.share) {
                                                                try {
                                                                    await navigator.share({
                                                                        title: 'My Award Nominations',
                                                                        text: redditText,
                                                                    });
                                                                } catch (err) {
                                                                    if (err.name !== 'AbortError') {
                                                                        this.copyToClipboard(redditText);
                                                                    }
                                                                }
                                                            } else {
                                                                this.copyToClipboard(redditText);
                                                            }
                                                        },
                                                        copyToClipboard(text) {
                                                            navigator.clipboard.writeText(text).then(() => {
                                                                alert('Your votes have been copied to your clipboard!');
                                                            }).catch(() => {
                                                                alert('Failed to copy your votes.');
                                                            });
                                                        }
                                                    }"
                                                    x-on:click="shareSelections()">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M13.5 1a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.499 2.499 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5zm-8.5 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm11 5.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/>
                                                    </svg>
                                                </span>
                                                <span>Share</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selectedCategory">
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
            
            /* Mobile Selections FAB */
            .mobile-selections-container {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 10000;
            }
            
            .mobile-selections-fab {
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: 64px;
                height: 48px;
                padding: 0 16px;
                border-radius: 24px;
                background: #2d3853;
                border: 2px solid #7EAEFF;
                color: #fff;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
                transition: all 0.3s ease;
                position: relative;
                font-size: 14px;
                font-weight: 600;
                white-space: nowrap;
            }
            
            .mobile-selections-fab:hover {
                background: #3d4a63;
                transform: scale(1.05);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.5);
            }
            
            .mobile-selections-fab.has-selections {
                background: #00D1B2;
                border-color: #00D1B2;
            }
            
            .mobile-selections-fab.has-selections:hover {
                background: #00b8a0;
                border-color: #00b8a0;
            }
            
            /* Bottom Sheet Overlay */
            .mobile-selections-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                z-index: 10001;
                backdrop-filter: blur(2px);
            }
            
            /* Bottom Sheet Panel */
            .mobile-selections-sheet {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                max-height: 70vh;
                background: #1B1E25;
                border-top-left-radius: 20px;
                border-top-right-radius: 20px;
                z-index: 10002;
                display: flex;
                flex-direction: column;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.5);
            }
            
            .mobile-selections-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 16px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .mobile-selections-header h3 {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 600;
            }
            
            .mobile-selections-close {
                background: transparent;
                border: none;
                color: #fff;
                cursor: pointer;
                padding: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                transition: background 0.2s;
            }
            
            .mobile-selections-close:hover {
                background: rgba(255, 255, 255, 0.1);
            }
            
            .mobile-selections-content {
                flex: 1;
                overflow-y: auto;
                padding: 16px 20px;
            }
            
            .mobile-selections-count {
                margin-bottom: 16px;
                padding-bottom: 12px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .mobile-selections-count p {
                margin: 0;
                font-size: 0.9rem;
            }
            
            .mobile-selections-list {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .mobile-selection-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 16px;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 8px;
                cursor: pointer;
                transition: background 0.2s;
            }
            
            .mobile-selection-item:hover {
                background: rgba(255, 255, 255, 0.1);
            }
            
            .mobile-selection-name {
                color: #fff;
                flex: 1;
                font-size: 0.9rem;
            }
            
            .mobile-selection-remove {
                background: transparent;
                border: none;
                color: #ff6b6b;
                cursor: pointer;
                padding: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                transition: background 0.2s;
                margin-left: 12px;
            }
            
            .mobile-selection-remove:hover {
                background: rgba(255, 107, 107, 0.2);
            }
            
            .mobile-selections-empty {
                text-align: center;
                padding: 32px 16px;
            }
            
            .mobile-selections-empty p {
                margin: 0;
                opacity: 0.6;
            }
            
            .mobile-selections-share {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }
        }
        
        @media (min-width: 1216px) {
            .mobile-selections-container {
                display: none;
            }
        }
    </style>
</div>