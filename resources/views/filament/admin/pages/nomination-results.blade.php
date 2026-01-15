<x-filament-panels::page>
    <div class="fi-page-content">
        <div class="fi-section">
            <div class="fi-section-content">
                <div class="fi-grid gap-4" style="--cols-sm: 1; --cols-md: 2; --cols-lg: 4;">
                    @foreach($this->categories as $category)
                        <div class="fi-grid-col" style="--col-span-sm: span 1 / span 1; --col-span-md: span 1 / span 1; --col-span-lg: span 1 / span 1;">
                            @livewire('category-vote-table', ['categoryId' => $category->id, 'categoryName' => $category->name], key('category-' . $category->id))
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
