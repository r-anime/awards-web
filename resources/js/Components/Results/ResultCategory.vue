<!-- TODO: FA Icons, Modal, Theme/Char/VA Titles, HMs -->
<template>
    <div :id="slug" class="awardDisplay mb-100">
    <!-- TODO: Implement Modals -->
		<div class="mb-6" @click="openCatModal(category)">
			<div class="is-pulled-left">
				<h2 class="categoryHeader title is-2 has-text-light pl-5" >
					{{category.name}}
				</h2>
			</div>
			<br class="is-hidden-desktop" />
			<button class="button is-platinum is-pulled-right mr-5 mt-3">
				<!-- Todo: FA Icons -->
        <span class="icon mr-4"><!--<fa-icon icon="info-circle" />--></span>
				Read Category Info
			</button>
		</div>
		<div class="is-clearfix my-3"></div>
        <result-winners v-if="nomPublicOrder[0] &&
            nomJuryOrder[0]"
            :pubWinner="nomPublicOrder[0]"
            :juryWinner="nomJuryOrder[0]"
            :category="category"
        />
            <!-- TODO: Implement Modals -->
            <!-- @nomModal="emitNomModal" -->
		<div class="">
			<div class="">
				<h5 class="is-pulled-left has-text-light is-size-4 ml-2">Nominees</h5>
				<div class="juryToggle is-pulled-right mr-2 is-inline-flex">
					<img class="image mr-2" src="/images/jury.png" width="42" height="34"/>
					<label class="switch">
						<input v-model="focus" type="checkbox">
						<span class="slider round">
						</span>
					</label>
					<img class="image" src="/images/public.png" width="42" height="34"/>
				</div>
			</div>
			<div class="is-clearfix my-3"></div>
      <!-- Final rankings -->
			<div>
					<transition-group name="nominees" tag="div" class="categoryNominationCards columns is-gapless is-marginless is-mobile is-multiline">
						<div class="categoryRankCard column is-half-mobile" v-for="(nom, index) in nomCurrentOrder"
						:key="nom.id" 
						@click="openNomModal(nom)"
            >
            <!-- Todo: implement modal -->
            <!-- @click="emitNomModal(nom)" -->
							<div class="categoryNominationItem" >
								<div class="categoryItemImage" :title="nom.id" :style="nomineeImage(nom)">
    						</div>
								<div class="nomineeTitle has-text-light is-size-6">
									<span>
                  {{nom.name}}
                  </span>
								</div>
							</div>
							<div class="categoryRank has-text-gold"
								style="background-image: url('/images/laurels.png')">
								{{index+1}}
							</div>
						</div>
					</transition-group>
					<small class="is-pulled-right has-text-light small mr-3">Click each nominee to read a detailed write-up.</small>
					<div class="is-clearfix"></div>
			</div>
		</div>
        <!-- Todo: Honorable Mentions -->
        <!-- <div class="awardHonorableMentions has-text-light has-text-centered my-6" v-if="category.hms && category.hms.length > 0">
            <h5 class="is-pulled-left has-text-light is-size-4 ml-2">Honorable Mentions</h5>
			      <div class="is-clearfix mb-4"></div>
            <div class="columns is-multiline mx-2 is-mobile">
				      <div class="column is-half-mobile is-one-quarter-tablet is-clickable" v-for="(hm, index) in category.hms" :key="index" @click="emitHMModal(hm)">
					      <div class="awardHonorableMention p-4">{{hm.name}}</div>
              </div>
            </div>
			      <small class="is-pulled-right has-text-light small mr-3">Click each HM to read a detailed write-up (if available).</small>
			      <div class="is-clearfix"></div>
        </div> -->
    </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import ResultWinners from './ResultWinners.vue';
const props = defineProps({ category: Object });
// ? Can be computed
const nomJuryOrder = props.category.results.slice().sort((nom1, nom2) => nom1.jury_rank - nom2.jury_rank);
const nomPublicOrder = props.category.results.slice().sort((nom1, nom2) => nom2.public_rank - nom1.public_rank);
let totalVotes = 0;

// Calculate public rank
nomPublicOrder.forEach((nom, index) => {
	nom.public_standing = index + 1;
	totalVotes += nom.public_rank;
});

// Calculate vote percentage
nomPublicOrder.forEach(nom => {
	nom.percent = nom.public_rank/totalVotes;
});

// Order toggle
const focus = ref(false);

const nomCurrentOrder = computed(() => {
  if(focus.value)
    return nomPublicOrder;
  return nomJuryOrder;
});

import { nomineeImage } from '../../utils.js';

const openNomModal = inject('openNomModal');
const openCatModal = inject('openCatModal');

</script>
