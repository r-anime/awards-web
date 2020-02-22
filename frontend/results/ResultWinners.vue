<template>
    <div>
        <div v-if="pub.id === jury.id" class="categoryWinnerContainer" >
            <div class="columns is-gapless">
                <div class="categoryWinnerItem categoryWinnerPublic categoryWinnerJury column">
                    <category-item-image
                        :nominee="pub"
                        :anilistData="anilistData"
                        :data="data"
                        :category="category"
                    />
                </div>
            </div>
        </div>
        <div v-else class="categoryWinnerContainer" >
            <div class="columns is-gapless">
                <div class="categoryWinnerItem categoryWinnerJury column">
                    <category-item-image
                        :nominee="jury"
                        :anilistData="anilistData"
                        :data="data"
                    />
                </div>
                <div class="categoryWinnerItem categoryWinnerPublic column">
                    <category-item-image
                        :nominee="pub"
                        :anilistData="anilistData"
                        :data="data"
                        :category="category"
                    />
                </div>
            </div>
        </div>
        <div class="categorySubHeadContainer columns is-gapless" v-if="pub.id !== jury.id">
            <div class="categorySubHeadItem categorySubHeadJury column">
                <div class="columns is-vcentered is-mobile is-multiline">
                    <div class="column is-narrow">
                        <div class="categorySubHeadItemIcon">
                            <img alt="laurels" :src="juryIcon" />
                        </div>
                    </div>
                    <div class="categorySubHeadItemText column is-narrow">
                        <h3 class="categorySubHeadItemTextTitle title is-4 has-text-llperiwinkle is-marginless">
                            <nominee-name
                            :nominee="jury"
                            :anilistData="anilistData"
                            :data="data"
                            :category="category"
                            ></nominee-name>
                        </h3>
                        <div class="categorySubHeadItemTextSubTitle has-text-gold">
                            Jury Winner
                        </div>
                    </div>
                </div>
            </div>
            <div class="categorySubHeadItem categorySubHeadPublic column">
                <div class="columns is-vcentered is-mobile is-multiline">
                    <div class="column is-narrow">
                        <div class="categorySubHeadItemIcon">
                            <img alt="snoo" :src="publicIcon" />
                        </div>
                    </div>
                    <div class="categorySubHeadItemText column is-narrow">
                        <h3 class="categorySubHeadItemTextTitle title is-4 has-text-llperiwinkle is-marginless">
                            <nominee-name
                            :nominee="pub"
                            :anilistData="anilistData"
                            :data="data"
                            :category="category"
                            ></nominee-name>
                        </h3>
                        <div class="categorySubHeadItemTextSubTitle has-text-gold">
                            Public Winner
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="categorySubHeadContainer" v-else>
            <div class="categorySubHeadItem categorySubHeadPublic categorySubHeadJury">
                <div class="columns is-centered is-vcentered">
                    <div class="column is-narrow">
                        <div class="categorySubHeadItemIcon">
                            <img alt="snoo with laurels" :src="consensusIcon" />
                        </div>
                    </div>
                    <div class="categorySubHeadItemText column is-narrow">
                        <h3 class="categorySubHeadItemTextTitle title is-4 has-text-llperiwinkle is-marginless">
                            <nominee-name
                            :nominee="pub"
                            :anilistData="anilistData"
                            :data="data"
                            :category="category"
                            ></nominee-name>
                        </h3>
                        <div class="categorySubHeadItemTextSubTitle has-text-gold">
                            Consensus Winner
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import CategoryItemImage from './ItemImage';
import NomineeName from './NomineeName';

import laurels from '../../img/laurels.png';
import juryIcon from '../../img/jury.png';
import publicIcon from '../../img/public.png';
import consensusIcon from '../../img/pubjury.png';

export default {
	props: ['pub', 'jury', 'anilistData', 'data', 'category'],
	components: {
		CategoryItemImage,
		NomineeName,
	},
	data () {
		return {
			laurels,
			juryIcon,
			publicIcon,
			consensusIcon,
		};
	},
};
</script>
