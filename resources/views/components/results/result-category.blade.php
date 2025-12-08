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
    <div x-data=" {
         results: {{ Js::from($resultcategory->results) }},
         get juryOrder() {
             return this.results.filter(nom => nom.jury_rank !== -1)
                 .sort((a, b) => a.jury_rank - b.jury_rank)
         },
         get publicOrder() {
             return this.results.filter(nom => nom.public_rank !== -1)
                 .sort((a, b) => b.public_rank - a.public_rank)
         },
         imgstyle(img) { return `background-image: url(/${img})` }
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
        <div x-data="{ focus: false }">
            {{-- Nominees --}}
            <div class="">
                {{-- Order toggle button --}}
                <h5 class="is-pulled-left has-text-light is-size-4 ml-2">Nominees</h5>
                <div class="juryToggle is-pulled-right mr-2 is-inline-flex">
                    <img class="image mr-2" src="{{ asset('images/jury.png') }}" width="42" height="34" />
                    <label class="switch">
                        <input x-model="focus" type="checkbox">
                        <span class="slider round">
                        </span>
                    </label>
                    <img class="image" src="{{ asset('images/public.png') }}" width="42" height="34" />
                </div>
            </div>
            <div class="is-clearfix my-3"></div>
            <div x-data="{ get nomineeOrder() { return (focus) ? publicOrder : juryOrder } }">
                {{-- Transition currently broken --}}
                <transition-group name="nominees" tag="div"
                    class="categoryNominationCards columns is-gapless is-marginless is-mobile is-multiline">
                    <template x-for="(nominee, rank) in nomineeOrder" :key="nominee.id">
                        <div class="categoryRankCard column is-half-mobile">
                            <div class="categoryNominationItem">
                                <div class="categoryItemImage" x-bind:title="nom.id"
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
                    <div class="is-clearfix"></div>
            </div>
        </div>
    </div>
</div>
