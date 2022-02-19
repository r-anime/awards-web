<template>
	<div :id="slug" class="awardSectionContainer py-6">
		<div class="awardSectionHeader has-text-centered has-text-light">
			<div class="is-flex is-justify-content-center is-align-items-center">
				<div class="sectionIconContainer">
					<fa-icon :icon="icon" size="7x" class="has-text-gold mb-20 mt-20" />
				</div>
				<div class="title-container">
					<h2 class="is-size-1 title has-text-light">{{section.name}} Awards</h2>
				</div>
			</div>
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
		@catModal="emitCatModal"
		/>
	</div>
</template>

<script>
import util from '../../util';
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
		emitNomModal (nom, ranking, category) {
			this.$emit('nomModal', nom, ranking, category);
		},
		emitHMModal (hm, category) {
			this.$emit('hmModal', hm, category);
		},
		emitCatModal (category) {
			this.$emit('hmModal', null, category);
		},
	},
};
</script>
