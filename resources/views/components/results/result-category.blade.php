{{--
    @var \App\Models\Category $resultcategory
--}}

<div id={{ Str::of($resultcategory->name)->lower()->kebab() }} class="awardDisplay mb-100">
    {{-- Component for a category within each result --}}
    <div class="mb-6">
        <div class="is-pulled-left">
            <h2 class="categoryHeader title is-2 has-text-light pl-5">
                {{ $resultcategory->name }}
            </h2>
        </div>
        <br>
        <br class="is-hidden-desktop" />
        {{-- Category info modal is disabled --}}
        {{-- <button class="button is-platinum is-pulled-right mr-5 mt-3">
            <span class="icon mr-4"><fa-icon icon="info-circle" /></span>
            Read Category Info
        </button> --}}
    </div>

    {{-- Nominees: Winners + Rankings --}}
    <div x-data="{
         results: {{ Js::from($resultcategory->results) }},
         get juryOrder() {
             return this.results.filter(nom => nom.jury_rank !== -1)
                 .sort((a, b) => a.jury_rank - b.jury_rank)
         },
         get publicOrder() {
             return this.results.filter(nom => nom.public_rank !== -1)
                 .sort((a, b) => b.public_rank - a.public_rank)
         },
         nomineeOrderArr: [],
         init() {
             this.nomineeOrderArr = this.juryOrder;
         },
         updateNomineeOrder(isPublic) {
             const container = this.$refs.nomineesContainer;
             const newOrder = isPublic ? this.publicOrder : this.juryOrder;
             const oldOrder = this.nomineeOrderArr;
             
             // Get IDs in each order
             const oldIds = new Set(oldOrder.map(n => n.id));
             const newIds = new Set(newOrder.map(n => n.id));
             const appearingIds = new Set([...newIds].filter(id => !oldIds.has(id)));
             const disappearingIds = new Set([...oldIds].filter(id => !newIds.has(id)));
             
             // FLIP animation: First - capture current positions
             const first = new Map();
             const items = Array.from(container.querySelectorAll('[data-nominee-id]'));
             items.forEach((el) => {
                 const id = parseInt(el.getAttribute('data-nominee-id'));
                 const rect = el.getBoundingClientRect();
                 first.set(id, {
                     top: rect.top,
                     left: rect.left,
                     width: rect.width,
                     height: rect.height
                 });
                 // Prepare for animation
                 el.style.willChange = 'transform, width';
                 
                 // Handle disappearing elements - animate width to 0
                 if (disappearingIds.has(id)) {
                     el.style.transition = 'width 0.4s cubic-bezier(0.4, 0, 0.2, 1), transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                     el.style.width = rect.width + 'px';
                     el.style.overflow = 'hidden';
                 }
             });
             
             // Animate out disappearing elements first
             if (disappearingIds.size > 0) {
                 requestAnimationFrame(() => {
                     items.forEach((el) => {
                         const id = parseInt(el.getAttribute('data-nominee-id'));
                         if (disappearingIds.has(id)) {
                             el.style.width = '0';
                             el.style.minWidth = '0';
                         }
                     });
                 });
                 
                 // Wait for disappear animation, then update array
                 setTimeout(() => {
                     this.nomineeOrderArr = newOrder;
                     
                     // Wait for DOM update, then animate appearing and reordering
                     requestAnimationFrame(() => {
                         container.offsetHeight; // Force layout
                         
                         // Animate appearing elements - width from 0 to full
                         const elements = Array.from(container.querySelectorAll('[data-nominee-id]'));
                         elements.forEach((el) => {
                             const id = parseInt(el.getAttribute('data-nominee-id'));
                             if (appearingIds.has(id)) {
                                 const rect = el.getBoundingClientRect();
                                 const finalWidth = rect.width;
                                 el.style.width = '0';
                                 el.style.minWidth = '0';
                                 el.style.overflow = 'hidden';
                                 el.style.transition = 'none';
                                 
                                 requestAnimationFrame(() => {
                                     el.style.transition = 'width 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                                     el.style.width = finalWidth + 'px';
                                     
                                     // Clean up after animation
                                     setTimeout(() => {
                                         el.style.width = '';
                                         el.style.minWidth = '';
                                         el.style.overflow = '';
                                     }, 500);
                                 });
                             }
                         });
                         
                         // Reorder existing elements (those that exist in both orders)
                         const updatedFirst = new Map();
                         elements.forEach((el) => {
                             const id = parseInt(el.getAttribute('data-nominee-id'));
                             if (!appearingIds.has(id) && !disappearingIds.has(id)) {
                                 const rect = el.getBoundingClientRect();
                                 const oldPos = first.get(id);
                                 if (oldPos) {
                                     updatedFirst.set(id, oldPos);
                                 }
                             }
                         });
                         
                         if (updatedFirst.size > 0) {
                             setTimeout(() => {
                                 this.animateReordering(container, updatedFirst, appearingIds);
                             }, 50);
                         }
                     });
                 }, 400);
             } else {
                 // Update the array immediately if no elements are disappearing
                 this.nomineeOrderArr = newOrder;
                 
                 // Wait for DOM update, then animate appearing and reordering
                 requestAnimationFrame(() => {
                     container.offsetHeight; // Force layout
                     
                     // Animate appearing elements
                     const elements = Array.from(container.querySelectorAll('[data-nominee-id]'));
                     elements.forEach((el) => {
                         const id = parseInt(el.getAttribute('data-nominee-id'));
                         if (appearingIds.has(id)) {
                             const rect = el.getBoundingClientRect();
                             const finalWidth = rect.width;
                             el.style.width = '0';
                             el.style.minWidth = '0';
                             el.style.overflow = 'hidden';
                             el.style.transition = 'none';
                             
                             requestAnimationFrame(() => {
                                 el.style.transition = 'width 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                                 el.style.width = finalWidth + 'px';
                                 
                                 // Clean up after animation
                                 setTimeout(() => {
                                     el.style.width = '';
                                     el.style.minWidth = '';
                                     el.style.overflow = '';
                                 }, 500);
                             });
                         }
                     });
                     
                     // Reorder existing elements
                     this.animateReordering(container, first, appearingIds);
                 });
             }
         },
         animateReordering(container, first, appearingIds) {
             // Force a synchronous layout recalculation to ensure DOM is updated
             container.offsetHeight;
             
             // Last - capture new positions
             const last = new Map();
             const elements = Array.from(container.querySelectorAll('[data-nominee-id]'));
             
             elements.forEach((el) => {
                 const id = parseInt(el.getAttribute('data-nominee-id'));
                 const rect = el.getBoundingClientRect();
                 last.set(id, {
                     top: rect.top,
                     left: rect.left,
                     width: rect.width,
                     height: rect.height
                 });
             });
             
             // Invert - apply transform immediately in same synchronous block to prevent flash
             elements.forEach((el) => {
                 const id = parseInt(el.getAttribute('data-nominee-id'));
                 const firstPos = first.get(id);
                 const lastPos = last.get(id);
                 
                 // Skip appearing elements - they'll be handled separately
                 if (appearingIds.has(id)) {
                     return;
                 }
                 
                 if (firstPos && lastPos) {
                     const deltaX = firstPos.left - lastPos.left;
                     const deltaY = firstPos.top - lastPos.top;
                     
                     if (deltaX !== 0 || deltaY !== 0) {
                         // Apply transform immediately with no transition to prevent flash
                         el.style.transition = 'none';
                         el.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
                     }
                 }
             });
             
             // Play - animate to final position in next frame
             requestAnimationFrame(() => {
                 elements.forEach((el) => {
                     const id = parseInt(el.getAttribute('data-nominee-id'));
                     if (!appearingIds.has(id)) {
                         el.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                         el.style.transform = '';
                     }
                 });
             });
         },
         imgstyle(img) { return `background-image: url(/storage/${img})` }
     }">
        {{-- <div x-text="JSON.stringify(results)"></div> --}}
        {{-- <div x-text="JSON.stringify(juryOrder[0])"></div> --}}
        {{-- <br><br> --}}
        {{-- <div x-text="JSON.stringify(publicOrder[0])"></div> --}}

        {{-- Winners --}}
        <div>
            <template x-if="juryOrder[0].id == publicOrder[0].id">
                {{-- Consensus Winner --}}
                <div>
                    {{-- Winner Image --}}
                    <div class="categoryWinnerContainer">
                        <div class="columns is-gapless is-mobile">
                            <div
                                class="categoryWinnerItem categoryWinnerPublic categoryWinnerJury column is-paddingless">
                                <div class="categoryItemImage" x-bind:title=juryOrder[0].id
                                    :style=imgstyle(juryOrder[0].image)></div>
                            </div>
                        </div>
                    </div>
                    <div class="categorySubHeadContainer level">
                        {{-- Winner Title --}}
                        <div
                            class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                            <div class="categorySubHeadItemIcon">
                                <img alt="laurels" src="{{ asset('images/pubjury.png') }}" />
                            </div>
                            <div class="categorySubHeadItemText">
                                <h3 class="categorySubHeadItemTextTitle title is-4 has-text-light">
                                    <span x-text=juryOrder[0].name>
                                    </span>
                                </h3>
                                <div class="categorySubHeadItemTextSubTitle has-text-llperiwinkle">
                                    Consensus Winner
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="juryOrder[0].id != publicOrder[0].id">
                <div>
                    {{-- Non-consensus winners --}}
                    <div class="categoryWinnerContainer">
                        {{-- Winner Images --}}
                        <div class="columns is-gapless is-mobile">
                            <div class="categoryWinnerItem categoryWinnerJury column is-paddingless">
                                <div class="categoryItemImage" x-bind:title=juryOrder[0].id
                                    :style=imgstyle(juryOrder[0].image)>
                                </div>
                            </div>
                            <div class="categoryWinnerItem categoryWinnerPublic column is-paddingless">
                                <div class="categoryItemImage" x-bind:title=publicOrder[0].id
                                    :style=imgstyle(publicOrder[0].image)>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="categorySubHeadContainer level">
                        {{-- Winner Titles --}}
                        <div
                            class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                            <div class="categorySubHeadItemIcon">
                                <img alt="laurels" src="{{ asset('images/jury.png') }}" />
                            </div>
                            <div class="categorySubHeadItemText">
                                <h3 class="categorySubHeadItemTextTitle title is-4 has-text-light">
                                    <span x-text=juryOrder[0].name></span>
                                </h3>
                                <div class="categorySubHeadItemTextSubTitle has-text-llperiwinkle">
                                    Jury Winner
                                </div>
                            </div>
                        </div>
                        <div
                            class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                            <div class="categorySubHeadItemIcon">
                                <img alt="laurels" src="{{ asset('images/public.png') }}" />
                            </div>
                            <div class="categorySubHeadItemText">
                                <h3 class="categorySubHeadItemTextTitle title is-4 has-text-light">
                                    <span x-text=publicOrder[0].name></span>
                                </h3>
                                <div class="categorySubHeadItemTextSubTitle has-text-llperiwinkle">
                                    Public Winner
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div>
            {{-- Nominees --}}
            <div class="">
                {{-- Order toggle button --}}
                <h5 class="is-pulled-left has-text-light is-size-4 ml-2">Nominees</h5>
                <div class="juryToggle is-pulled-right mr-2 is-inline-flex">
                    <img class="image mr-2" src="{{ asset('images/jury.png') }}" width="42" height="34" />
                    <label class="switch">
                        <input type="checkbox" @change="updateNomineeOrder($event.target.checked)">
                        <span class="slider round">
                        </span>
                    </label>
                    <img class="image" src="{{ asset('images/public.png') }}" width="42" height="34" />
                </div>
            </div>
            <div class="is-clearfix my-3"></div>
            <div>
                <div class="categoryNominationCards columns is-gapless is-marginless is-mobile is-multiline" x-ref="nomineesContainer">
                    <template x-for="(nominee, rank) in nomineeOrderArr" :key="nominee.id">
                        <div class="categoryRankCard column is-half-mobile" :data-nominee-id="nominee.id">
                            <div class="categoryNominationItem">
                                <div class="categoryItemImage" x-bind:title="nominee.id"
                                    :style=imgstyle(nominee.image)>
                                </div>
                                <div class="nomineeTitle has-text-light is-size-6">
                                    <span x-text=nominee.name></span>
                                </div>
                            </div>
                            <div class="categoryRank has-text-gold" x-text=rank+1;
                                style="background-image: url({{ asset('images/laurels.png') }})">

                            </div>
                        </div>
                    </template>
                </div>
                <div class="is-clearfix"></div>
            </div>
        </div>
    </div>
</div>
