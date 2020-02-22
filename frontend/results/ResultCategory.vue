<template>
    <div :id="slug" class="awardDisplay">
        <h2 class="categoryHeader title is-3 has-text-gold has-text-centered mt-100 pb-10 mb-20">{{category.name}}</h2>
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
            <div class="hero-body">
                <div class="container">
                    <div class="tabs is-fullwidth has-text-centered">
                        <ul >
                            <li @click="juryOrder" class="is-gold" :class="{'is-active': focus === 'jury'}">
                                Jury Rankings
                            </li>
                            <li @click="publicOrder" class="is-periwinkle" :class="{'is-active': focus === 'public'}">
                                Public Rankings
                            </li>
                        </ul>
                    </div>
                    <div>
                        <div class="categoryNominationCards columns is-gapless is-marginless" :class="{'is-hidden': focus === 'jury'}">
                            <div class="column" v-for="(nom, index) in nomPublicOrder"
                            :key="index" @click="emitNomModal(nom)">
                                <span class="has-text-light">{{nomPublicRankings[index]}}</span>
                                <div class="categoryNominationItem" >
                                    <category-item-image :nominee="nom" :anilistData="anilistData" />
                                </div>
                                <p v-html="markdownit(nom.writeup.substring(0, 69))" class="categoryNominationPreview has-text-llperiwinkle has-text-left"></p>
                            </div>
                        </div>
                        <div class="categoryNominationCards columns is-gapless" :class="{'is-hidden': focus === 'public'}">
                            <div class="column" v-for="(nom, index) in nomJuryOrder"
                            :key="index" @click="emitNomModal(nom)">
                                <span class="has-text-light">{{index + 1}}</span>
                                <div class="categoryNominationItem" >
                                    <category-item-image :nominee="nom" :anilistData="anilistData" />
                                </div>
                                <p v-html="markdownit(nom.writeup.substring(0, 69))" class="categoryNominationPreview has-text-llperiwinkle has-text-left"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="awardHonorableMentions has-text-light has-text-centered mt-50" v-if="category.hms && category.hms.length > 0">
            <h4 class="categoryHeader title has-text-light has-flaired-underline-thin pb-20">Honorable Mentions</h4>
            <ul>
                <li v-for="(hm, index) in category.hms" :key="index" @click="emitHMModal(hm)">{{hm.name}}</li>
            </ul>
        </div>
    </div>
</template>

<script>
import util from '../util';
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
			console.log(po);
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
	},
	methods: {
		publicOrder () {
			this.focus = 'public';
		},
		juryOrder () {
			this.focus = 'jury';
		},
		emitNomModal (nom) {
			const ranking = this.getPRgivenShow(nom);
			this.$emit('nomModal', nom, ranking, this.category);
		},
		emitHMModal (hm) {
			this.$emit('hmModal', hm, this.category);
		},
		markdownit (writeup) {
			return marked(writeup);
		},
		getPRgivenShow (nominee) {
			const index = this.nomPublicOrder.findIndex(nom => nom.id === nominee.id);
			return this.nomPublicRankings[index];
		},
	},
	mounted () {
		console.log(this.category);
	},
};
</script>
