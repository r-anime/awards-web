<!-- Todo: Category, Jurors, Ranks, Vote%   -->
<template>
    <div class="modal animated fast fadeIn" :class="{ 'is-active': modalNom && showNom }" v-if="modalNom">
        <div class="modal-background" @click="closeModal"></div>
        <div class="modal-content">
            <div class="columns is-gapless">
                <div class="awardsImage column is-5">
                    <div class="categoryItemImage" :title="modalNom.id" :style="itemImage(modalNom)">
                    </div>
                </div>
                <div class="column is-7">
                    <div class="awardsModal has-text-light has-background-dark content">
                        <h3 class="categorySubHeadItemTextTitle title is-4 has-text-gold mb-10">
                            <span>{{ nominee.name }}</span>
                            <!-- <span v-if="modalCat.entryType==='themes'">
                                {{results.themes[modalNom.id].split(/ - /gm)[1]}} ({{results.themes[modalNom.id].split(/ - /gm)[0]}})
                            </span>
                            <span>
                                {{nomineeName(modalNom, this.modalCat)}}
                            </span>
                            <span v-if="modalCat.entryType==='characters' && !modalNom.altname">
                                ({{results.characters[modalNom.id].anime}})
                            </span>
                            <span v-if="modalCat.entryType==='vas' && !modalNom.altname">
                                ({{results.characters[modalNom.id].va}})
                            </span> -->
                        </h3>
                        <div class="is-marginless">
                            <!-- <div class="awardRanksContainer columns is-size-7 is-centered is-vcentered has-text-silver">
                                <div v-if="modalNom.percent > 0" class="column is-narrow"> <img class="image" :src="publicIcon" /> </div>
                                <div v-if="modalNom.percent > 0" class="column "> Public {{prettifyRank(modalRank)}} ({{(modalNom.percent*100).toFixed(2)}}%) </div>
                                <div v-if="modalNom.jury > 0" class="column is-narrow"> <img class="image" :src="juryIcon" /> </div>
                                <div v-if="modalNom.jury > 0" class="column"> Jury {{prettifyRank(modalNom.jury)}} </div>
                            </div> -->
                        </div>
                        <p class="awardsStaffCredit has-text-llperiwinkle is-size-6"
                            v-html="markdownit(nominee.staff_credits)">
                        </p>
                        <div class="awardsModalBody" v-html="markdownit(nominee.description)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="modal-close is-large" aria-label="close" @click="closeModal"></button>
    </div>
    <!-- Todo: HM Modal -->
    <!-- <div class="modal animated fast fadeIn" :class="{ 'is-active': modalHM && showHM }" v-if="modalHM">
        <div class="modal-background" @click="closeModal"></div>
        <div class="modal-content">
            <div class="awardsModal has-text-light has-background-dark content">
                <h3 class="categorySubHeadItemTextTitle title is-4 has-text-gold mb-10">
                    {{ modalHM.name }}
                </h3>
                <div class="awardsModalBody" v-html="markdownit(modalHM.writeup)">
                </div>
            </div>
        </div>
        <button class="modal-close is-large" aria-label="close" @click="closeModal"></button>
    </div> -->
    <!-- Todo: Category Modal -->
    <!-- <div class="modal animated fast fadeIn" :class="{ 'is-active': modalCat && showCat }" v-if="modalCat">
        <div class="modal-background" @click="closeModal"></div>
        <div class="modal-content">
            <div class="awardsModal has-text-light has-background-dark content">
                <h3 class="categorySubHeadItemTextTitle title is-4 has-text-gold mb-10">
                    {{ modalCat.name }}
                </h3>
                <div class="awardsModalBody mt-30" v-html="markdownit(modalCat.blurb)">
                </div>
                <h5 class="title is-5 mt-30"> Vote Data </h5>
                <table width="100%" class="table is-black-bis " v-if="chartData">
                    <thead>
                        <tr>
                            <th> Show </th>
                            <th> Votes </th>
                            <th v-if="chartData.pubnoms[0].watched"> Watched </th>
                            <th v-else-if="chartData.pubnoms[0].finished > 0"> Watched </th>
                            <th v-if="(chartData.pubnoms[0].support * 100).toFixed(2) > 0" class="is-hidden-mobile">
                                Support % </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(label, index) in chartData.labels" :key="index">
                            <th>
                                {{ label }}
                            </th>
                            <th>
                                {{ chartData.pubnoms[index].public }}
                                ({{ (chartData.pubnoms[index].percent * 100).toFixed(2) }}%)
                            </th>
                            <th v-if="chartData.pubnoms[index].watched">
                                {{ chartData.pubnoms[index].watched }}
                            </th>
                            <th v-else-if="chartData.pubnoms[index].finished > 0">
                                {{ chartData.pubnoms[index].finished }}
                            </th>
                            <th v-if="(chartData.pubnoms[index].support * 100).toFixed(2) > 0" class="is-hidden-mobile">
                                {{ (chartData.pubnoms[index].support * 100).toFixed(2) }} %
                            </th>
                        </tr>
                    </tbody>
                </table>
                <div class="categoryJurors mt-30">
                    <h5 class="title is-5"> Jurors </h5>
                    <div class="tags">
                        <span class="mr-10" v-for="(juror, index) in modalCat.jurors" :key="index">
                            <a class="tag has-text-black is-platinum"
                                v-if="typeof juror === 'string' && !juror.startsWith('/u/')"
                                :href="'https://reddit.com/u/' + juror">
                                {{ juror }}
                            </a>
                            <a class="tag has-text-black is-platinum"
                                v-else-if="typeof juror === 'string' && juror.startsWith('/u/')"
                                :href="'https://reddit.com' + juror">
                                {{ juror }}
                            </a>
                            <a class="tag has-text-black is-platinum" v-else :href="juror.link">
                                {{ juror.name }}
                            </a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <button class="modal-close is-large" aria-label="close" @click="closeModal"></button>
    </div> -->
</template>

<script setup>
import { ref, provide } from 'vue';
// const props = defineProps({ type: String, nominee: Object | null, category: Object | null })
const type = ref('');
const nominee = ref(null);
const category = ref(null);
const hm = ref(null);

function closeModal() {
    const modalels = document.getElementsByClassName('awardsModal');
			[].forEach.call(modalels, el => {
				// console.log(el);
				el.scrollTop = 0;
			});
            nominee.value = null;
            category.value = null;
            hm.value = null;
			document.documentElement.classList.remove('is-clipped');
}

</script>