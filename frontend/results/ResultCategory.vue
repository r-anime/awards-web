<template>
    <div :id="slug" class="awardDisplay">
        <h2 class="categoryHeader title is-3 has-text-gold has-text-centered mt-100 pb-10 mb-20">{{category.name}}</h2>
        <award-winners v-if="nomPublicOrder[0] && nomJuryOrder[0]" :pub="nomPublicOrder[0]" :jury="nomJuryOrder[0]" :anilistData="anilistData" />
        <section class="hero has-background-black-bis">
            <div class="hero-body">
                <div class="container">
                    <div class="columns is-centered is-gapless is-marginless">
                        <div class="column">
                            <button @click="juryOrder" class="button has-text-weight-bold is-medium is-fullwidth" :class="{'has-text-black-bis has-background-gold': focus === 'jury', 'has-background-black-bis has-text-gold' : focus !== 'jury'}">Jury Rankings</button>
                        </div>
                        <div class="column">
                            <button @click="publicOrder" class="button has-text-weight-bold is-medium is-fullwidth" :class="{'has-background-periwinkle has-text-light': focus === 'public', 'has-background-black-bis has-text-periwinkle': focus !== 'public'}">Public Rankings</button>
                        </div>
                    </div>
                    <div class="categoryNominationCards">
                        <div class="categoryNominationItem" v-for="(nom, index) in nomPublicOrder" :key="index">
                            <category-item-image :nominee="nom" :anilistData="anilistData" />
                        </div>
                    </div>
                    <div class="categoryNominationCards">
                        <div class="categoryNominationItem" v-for="(nom, index) in nomJuryOrder" :key="index">
                            <category-item-image :nominee="nom" :anilistData="data" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="awardHonorableMentions" v-if="category.hms && category.hms.length > 0">
            <h4>Honorable Mentions</h4>
            <ul>
                <li v-for="(hm, index) in category.hms" :key="index">{{hm}}</li>
            </ul>
        </div>
    </div>
</template>

<script>
import util from '../util';
import AwardWinners from './ResultWinners';
import CategoryItemImage from './ItemImage';

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
			nomPublicOrder: [].concat(this.category.nominees).sort((a, b) => b.public - a.public),
			nomJuryOrder: [].concat(this.category.nominees).sort((a, b) => a.jury - b.jury),
			focus: 'public',
		};
	},
	computed: {
		slug () {
			return `category-${util.slugify(this.category.name)}`;
		},
	},
	methods: {
		publicOrder () {
			this.focus = 'public';
		},
		juryOrder () {
			this.focus = 'jury';
		},
	},
	mounted () {
		// console.log(this.anilistData);
	},
};
</script>

<style lang="scss" scoped>
.button {
	border-radius: 0px;
}
</style>
