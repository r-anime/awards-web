<template>
	<div :id="slug" class="awardSectionContainer">
		<div class="awardSectionHeader has-text-centered has-text-light">
			<div class="sectionIconContainer">
				<fa-icon :icon="icon" size="6x" class="has-text-gold mb-20 mt-20" />
			</div>
			<h2 class="sectionHeader title is-2 has-text-light pb-20 has-flaired-underline">{{section.name}} Awards</h2>
				<div
				v-if="typeof section.blurb === 'string'"
				class="awardSectionBlurb"
				html="blurb"
				/>
			<div v-else>
				<div
				v-for="(blurb, index) in section.blurb"
				class="awardSectionBlurb"
				:key="index"
				v-html="blurb"
				/>
			</div>
		</div>
		<awards-category
		v-for="(category, index) in section.awards"
		:key="index"
		:category="category"
		:data="data"
		:anilistData="(category.entryType === 'shows' || category.entryType === 'themes') ? showData : charData"
		@nomModal="emitNomModal"
		@hmModal="emitHMModal"
		/>
	</div>
</template>

<script>
import util from '../util';
import AwardsCategory from './ResultCategory';
// import GenreIcon from '../../img/genreawards.png';
// import CharIcon from '../../img/characterawards.png';
// import ProdIcon from '../../img/productionawards.png';

export default {
	props: [
		'section',
		'data',
		'showData',
		'charData',
	],
	components: {
		AwardsCategory,
	},
	data () {
		return {
			util,
		};
	},
	computed: {
		slug () {
			return `section-${util.slugify(this.section.name)}`;
		},
		icon () {
			switch (this.section.slug) {
				case 'genre':
					return 'book';
				case 'production':
					return 'pencil-ruler';
				case 'character':
					return 'user-friends';
				default:
					return 'crown';
			}
		},
	},
	methods: {
		emitNomModal (nom) {
			this.$emit('nomModal', nom);
		},
		emitHMModal (hm) {
			this.$emit('hmModal', hm);
		},
	},
	mounted () {
		// console.log(this.showData, this.charData);
	},
};
</script>
