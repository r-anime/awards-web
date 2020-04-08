<template>
    <div>
        <div v-if="pub.id === jury.id" class="categoryWinnerContainer" >
            <div class="columns is-gapless">
                <div class="categoryWinnerItem categoryWinnerPublic categoryWinnerJury column is-paddingless" @click="emitNomModal(pub)">
                    <category-item-image
                        :nominee="pub"
                        :anilistData="anilistData"
                    />
                </div>
            </div>
        </div>
        <div v-else class="categoryWinnerContainer" >
            <div class="columns is-gapless">
                <div class="categoryWinnerItem categoryWinnerJury column is-paddingless" @click="emitNomModal(jury)">
                    <category-item-image
                        :nominee="jury"
                        :anilistData="anilistData"
                    />
                </div>
                <div class="categoryWinnerItem categoryWinnerPublic column is-paddingless" @click="emitNomModal(pub)">
                    <category-item-image
                        :nominee="pub"
                        :anilistData="anilistData"
                    />
                </div>
            </div>
        </div>
        <div class="categorySubHeadContainer level" v-if="pub.id !== jury.id">
            <div class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                    <div class="categorySubHeadItemIcon">
                        <img alt="laurels" :src="juryIcon" />
                    </div>
                    <div class="categorySubHeadItemText">
                        <h3 class="categorySubHeadItemTextTitle title is-4 has-text-llperiwinkle">
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
            <div class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                    <div class="categorySubHeadItemIcon">
                        <img alt="laurels" :src="publicIcon" />
                    </div>
                    <div class="categorySubHeadItemText">
                        <h3 class="categorySubHeadItemTextTitle title is-4 has-text-llperiwinkle">
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
        <div class="categorySubHeadContainer" v-else>
            <div class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                <div class="categorySubHeadItemIcon">
                    <img alt="laurels" :src="consensusIcon" />
                </div>
                <div class="categorySubHeadItemText">
                    <h3 class="categorySubHeadItemTextTitle title is-4 has-text-llperiwinkle">
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
</template>
<script>
import CategoryItemImage from './ItemImage';
import NomineeName from './NomineeName';

import laurels from '../../../img/laurels.png';
import juryIcon from '../../../img/jury.png';
import publicIcon from '../../../img/public.png';
import consensusIcon from '../../../img/pubjury.png';

export default {
	props: ['pub', 'jury', 'anilistData', 'data', 'category'],
	components: {
		CategoryItemImage,
		NomineeName,
	},
	methods: {
		emitNomModal (nom) {
			this.$emit('nomModal', nom);
		},
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
