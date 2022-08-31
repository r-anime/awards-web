<template>
    <div>
        <div v-if="pub.id === jury.id" class="categoryWinnerContainer" >
            <div class="columns is-gapless is-mobile">
                <div class="categoryWinnerItem categoryWinnerPublic categoryWinnerJury column is-paddingless" @click="emitNomModal(pub)">
                    <category-item-image
                        :nominee="pub"
                        :anilistData="anilistData"
                    />
                </div>
            </div>
        </div>
        <div v-else class="categoryWinnerContainer" >
            <div class="columns is-gapless is-mobile">
                <div class="categoryWinnerItem categoryWinnerJury column is-paddingless" @click="emitNomModal(jury)">
                    <div class="categoryItemImage" :title="jury.id" :style="itemImage(jury)">
    				</div>
                </div>
                <div class="categoryWinnerItem categoryWinnerPublic column is-paddingless" @click="emitNomModal(pub)">
                    <div class="categoryItemImage" :title="pub.id" :style="itemImage(pub)">
    				</div>
                </div>
            </div>
        </div>
        <div class="categorySubHeadContainer level" v-if="pub.id !== jury.id">
            <div class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                    <div class="categorySubHeadItemIcon">
                        <img alt="laurels" :src="juryIcon" />
                    </div>
                    <div class="categorySubHeadItemText">
                        <h3 class="categorySubHeadItemTextTitle title is-4 has-text-light">
                            <span v-if="category.entryType==='themes'">
							{{data.themes[jury.id].split(/ - /gm)[1]}} ({{data.themes[jury.id].split(/ - /gm)[0]}})
							</span>
                            <nominee-name v-else
                            :nominee="jury"
                            :anilistData="anilistData"
                            :data="data"
                            :category="category"
                            ></nominee-name>
                            <span v-if="category.entryType==='characters'">
							({{data.characters[jury.id].anime}})
							</span>
							<span v-if="category.entryType==='vas'">
							({{data.characters[jury.id].va}})
							</span>
                        </h3>
                        <div class="categorySubHeadItemTextSubTitle has-text-llperiwinkle">
                            Jury Winner
                        </div>
                    </div>
            </div>
            <div class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                    <div class="categorySubHeadItemIcon">
                        <img alt="laurels" :src="publicIcon" />
                    </div>
                    <div class="categorySubHeadItemText">
                        <h3 class="categorySubHeadItemTextTitle title is-4 has-text-light">
                            <span v-if="category.entryType==='themes'">
							{{data.themes[pub.id].split(/ - /gm)[1]}} ({{data.themes[pub.id].split(/ - /gm)[0]}})
							</span>
                            <nominee-name v-else
                            :nominee="pub"
                            :anilistData="anilistData"
                            :data="data"
                            :category="category"
                            ></nominee-name>
                            <span v-if="category.entryType==='characters'">
							({{data.characters[pub.id].anime}})
							</span>
							<span v-if="category.entryType==='vas'">
							({{data.characters[pub.id].va}})
							</span>
                        </h3>
                        <div class="categorySubHeadItemTextSubTitle has-text-llperiwinkle">
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
                    <h3 class="categorySubHeadItemTextTitle title is-4 has-text-light">
                        <nominee-name
                        :nominee="pub"
                        :anilistData="anilistData"
                        :data="data"
                        :category="category"
                        ></nominee-name>
                    </h3>
                    <div class="categorySubHeadItemTextSubTitle has-text-llperiwinkle">
                        Consensus Winner
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import NomineeName from './NomineeName';

import laurels from '../../../img/laurels.png';
import juryIcon from '../../../img/jury.png';
import publicIcon from '../../../img/public.png';
import consensusIcon from '../../../img/pubjury.png';

export default {
	props: ['pub', 'jury', 'anilistData', 'data', 'category'],
	components: {
		NomineeName,
	},
	methods: {
		emitNomModal (nom) {
			this.$emit('nomModal', nom);
		},
        itemImage (nom) {
			if (this.anilistData) {
				if (nom.altimg !== '') {
					return `background-image: url(${nom.altimg})`;
				}
				const found = this.anilistData.find(el => el.id === nom.id);
				if (found && found.image) {
					if (found.image.extraLarge) {
						return `background-image: url(${found.coverImage.extraLarge})`;
					}
					if (found.image.large) {
						return `background-image: url(${found.image.large})`;
					}
				}
				if (found && found.coverImage) {
					if (found.coverImage.extraLarge) {
						return `background-image: url(${found.coverImage.extraLarge})`;
					}
					if (found.coverImage.large) {
						return `background-image: url(${found.coverImage.large})`;
					}
				}
			}
			return 'background-image: none';
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
