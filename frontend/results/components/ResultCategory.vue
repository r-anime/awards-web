<template>
    <div :id="slug" class="awardDisplay mb-100">
		<div class="has-text-centered mb-30" @click="emitCatModal">
			<h2 class="categoryHeader title is-3 has-text-gold has-text-centered mt-50 mb-10" >
				{{category.name}}
            </h2>
			<button class="button is-small is-platinum is-outlined">
				<span class="icon is-small mr-10"><fa-icon icon="info-circle" /></span>
				Category Overview
			</button>
		</div>
        <award-winners v-if="nomPublicOrder[0] &&
            nomJuryOrder[0]"
            :pub="nomPublicOrder[0]"
            :jury="nomJuryOrder[0]"
            :anilistData="anilistData"
            :data="data"
            :category="category"
            @nomModal="emitNomModal"
        />
        <section class="hero has-background-black-bis">
            <div class="ranking-container">
                <div class="container">
					<h5 class="has-text-centered has-text-light is-size-5">Rankings</h5>
                    <div class="tabs is-fullwidth has-text-centered has-text-grey">
                        <ul >
                            <li @click="juryOrder" class="is-gold" :class="{'is-active': focus === 'jury'}">
                                Jury
                            </li>
                            <li @click="publicOrder" class="is-periwinkle" :class="{'is-active': focus === 'public'}">
                                Public
                            </li>
                        </ul>
                    </div>
                    <div>
							<transition-group name="nominees" tag="div" class="categoryNominationCards columns is-gapless is-marginless">
								<div class="categoryRankCard column" v-for="(nom, index) in nomCurrentOrder"
								:key="nom.id" @click="emitNomModal(nom)">
									<div class="categoryNominationItem" >
										<category-item-image :nominee="nom" :anilistData="anilistData" :data="data" />
									</div>
									<div class="categoryRank has-text-platinum">{{nomPublicRankings[index]}}</div>
									<button class="button is-small is-platinum is-outlined is-hidden-tablet mb-10">
										<span class="icon is-small mr-10"><fa-icon icon="info-circle" /></span>
										Read Nominee Writeup
									</button>
									<p v-html="markdownit(nom.writeup.substring(0, 69))" class="categoryNominationPreview has-text-light has-text-left"></p>
								</div>
							</transition-group>
                    </div>
                </div>
            </div>
        </section>
        <div class="awardHonorableMentions has-text-light has-text-centered mt-20" v-if="category.hms && category.hms.length > 0">
            <h4 class="categoryHeader title has-text-light has-flaired-underline-thin pb-20">Honorable Mentions</h4>
            <ul>
				<li v-for="(hm, index) in category.hms" :key="index" @click="emitHMModal(hm)">
					<div class="has-text-centered mb-30">
						<div>{{hm.name}}</div>
						<button v-if="hm.writeup !== ''" class="button is-small is-llperiwinkle is-outlined mt-10">
							<span class="icon is-small mr-10"><fa-icon icon="info-circle" /></span>
							Read Write-up
						</button>
					</div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import {mapActions} from 'vuex';
import util from '../../util';
import AwardWinners from './ResultWinners';
import CategoryItemImage from './ItemImage';
import marked from 'marked';

export default {
	props: [
		'category',
		'data',
		'showData',
		'charData',
		'anilistData',
	],
	components: {
		AwardWinners,
		CategoryItemImage,
	},
	data () {
		return {
			focus: 'public',
		};
	},
	computed: {
		slug () {
			return `category-${util.slugify(this.category.name)}`;
		},
		nomPublicOrder () {
			return [].concat(this.category.nominees).filter(nom => nom.public !== -1).sort((a, b) => b.public - a.public);
		},
		nomJuryOrder () {
			return [].concat(this.category.nominees).filter(nom => nom.jury !== -1).sort((a, b) => a.jury - b.jury);
		},
		nomPublicRankings () {
			const po = [].concat(this.category.nominees).filter(nom => nom.public !== -1).sort((a, b) => b.public - a.public);
			const ranking = [];
			for (let i = 0; i < po.length; i++) {
				ranking.push(i + 1);
				if (i < po.length - 1 && typeof po[i].public !== 'undefined' && po[i + 1].public && po[i].public === po[i + 1].public) {
					ranking.push(i + 1);
					i++;
				}
			}
			return ranking;
		},
		nomCurrentOrder () {
			if (this.focus === 'public') {
				return this.nomPublicOrder;
			}
			return this.nomJuryOrder;
		},
	},
	methods: {
		...mapActions(['sendAnalytics']),
		publicOrder () {
			this.focus = 'public';
		},
		juryOrder () {
			this.focus = 'jury';
		},
		emitNomModal (nom) {
			const ranking = this.getPRgivenShow(nom);
			const _catname = this.category.name;
			const _nomid = nom.id;
			const _juryrank = nom.jury;
			this.$emit('nomModal', nom, ranking, this.category);
		},
		emitHMModal (hm) {
			const _hmname = hm.name;
			this.$emit('hmModal', hm, this.category);
		},
		emitCatModal () {
			this.$emit('hmModal', null, this.category);
		},
		markdownit (writeup) {
			return marked(writeup);
		},
		getPRgivenShow (nominee) {
			const index = this.nomPublicOrder.findIndex(nom => nom.id === nominee.id);
			return this.nomPublicRankings[index];
		},
		beforeLeave (el) {
			const {marginLeft, marginTop, width, height} = window.getComputedStyle(el);

			el.style.left = `${el.offsetLeft - parseFloat(marginLeft, 10)}px`;
			el.style.top = `${el.offsetTop - parseFloat(marginTop, 10)}px`;
			el.style.width = width;
			el.style.height = height;
		},
	},
};
</script>
