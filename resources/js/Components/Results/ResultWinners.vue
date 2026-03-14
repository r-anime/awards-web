<!-- TODO: Theme/Char/VA Titles -->
<template>
    <div>
		<!-- Consensus Winner -->
        <div v-if="pubWinner.id === juryWinner.id" class="categoryWinnerContainer" >
            <div class="columns is-gapless is-mobile">
                <div class="categoryWinnerItem categoryWinnerPublic categoryWinnerJury column is-paddingless" @click="openNomModal(pubWinner)">
                    <div class="categoryItemImage" :title="pubWinner.id" :style="nomineeImage(pubWinner)">
    				</div>
                </div>
            </div>
        </div>
		<!-- Non Consensus Winner -->
        <div v-else class="categoryWinnerContainer" >
            <div class="columns is-gapless is-mobile">
                <div class="categoryWinnerItem categoryWinnerJury column is-paddingless" @click="openNomModal(juryWinner)">
                    <div class="categoryItemImage" :title="juryWinner.id" :style="nomineeImage(juryWinner)">
    				</div>
                </div>
                <div class="categoryWinnerItem categoryWinnerPublic column is-paddingless" @click="openNomModal(pubWinner)">
                    <div class="categoryItemImage" :title="pubWinner.id" :style="nomineeImage(pubWinner)">
    				</div>
                </div>
            </div>
        </div>
        <!-- Consensus winner titles-->
        <div class="categorySubHeadContainer" v-if="pubWinner.id == juryWinner.id">
            <div class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                <div class="categorySubHeadItemIcon">
                    <img alt="laurels" src="/images/pubjury.png" />
                </div>
                <div class="categorySubHeadItemText">
                    <h3 class="categorySubHeadItemTextTitle title is-4 has-text-light">
                        <span>
                            {{pubWinner.name}}
                        </span>
                    </h3>
                    <div class="categorySubHeadItemTextSubTitle has-text-llperiwinkle">
                        Consensus Winner
                    </div>
                </div>
            </div>
        </div>
		<!--  Non consensus winner titles -->
        <div class="categorySubHeadContainer level" v-else>
            <!-- Jury winner title -->
            <div class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                    <div class="categorySubHeadItemIcon">
                        <img alt="laurels" src="/images/jury.png" />
                    </div>
                    <div class="categorySubHeadItemText">
                        <h3 class="categorySubHeadItemTextTitle title is-4 has-text-light">
                            {{juryWinner.name}}
                            <!-- <span v-if="category.entryType==='themes'">
							{{data.themes[jury.id].split(/ - /gm)[1]}} ({{data.themes[jury.id].split(/ - /gm)[0]}})
							</span>
                            <span v-else>
                            {{nomineeName(jury)}}
                            </span>
                            <span v-if="category.entryType==='characters' && jury.altName==''">
							({{data.characters[jury.id].anime}})
							</span>
							<span v-if="category.entryType==='vas' && jury.altName==''">
							({{data.characters[jury.id].va}})
							</span> -->
                        </h3>
                        <div class="categorySubHeadItemTextSubTitle has-text-llperiwinkle">
                            Jury Winner
                        </div>
                    </div>
            </div>
            <!-- Public winner title -->
            <div class="categorySubHeadItem categorySubHeadJury level-item is-centered has-text-centered-touch">
                    <div class="categorySubHeadItemIcon">
                        <img alt="laurels" src="/images/public.png" />
                    </div>
                    <div class="categorySubHeadItemText">
                        <h3 class="categorySubHeadItemTextTitle title is-4 has-text-light">
                            {{pubWinner.name}}
                            <!-- <span v-if="category.entryType==='themes'">
							{{data.themes[pub.id].split(/ - /gm)[1]}} ({{data.themes[pub.id].split(/ - /gm)[0]}})
							</span>
                            <span v-else>
                            {{nomineeName(pub)}}
                            </span>
                            <span v-if="category.entryType==='characters' && pub.altname==''">
							({{data.characters[pub.id].anime}})
							</span>
							<span v-if="category.entryType==='vas' && !pub.altname==''">
							({{data.characters[pub.id].va}})
							</span> -->
                        </h3>
                        <div class="categorySubHeadItemTextSubTitle has-text-llperiwinkle">
                            Public Winner
                        </div>
                    </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject } from 'vue';
const props = defineProps({ category: Object, juryWinner: Object, pubWinner: Object })
import { nomineeImage } from '../../utils.js';

const openNomModal = inject('openNomModal');

</script>