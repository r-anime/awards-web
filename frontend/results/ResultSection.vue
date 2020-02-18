<template>
    <div :id="slug" class="awardSectionContainer container">
        <div class="awardSectionHeader has-text-centered has-text-light">
            <div class="sectionIconContainer"><img class="sectionIcon" :alt="section.name" :src="icon" /></div>
            <h1 class="sectionHeader title is-2 has-text-light">{{section.name}} Awards</h1>
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
        />
    </div>
</template>

<script>
import util from '../util';
import AwardsCategory from './ResultCategory';
import GenreIcon from '../../img/genreawards.png';
import CharIcon from '../../img/characterawards.png';
import ProdIcon from '../../img/productionawards.png';

export default {
	props: ['section'],
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
					return GenreIcon;
				case 'production':
					return ProdIcon;
				case 'character':
					return CharIcon;
				default:
					return '';
			}
		},
	},
	mounted () {
		console.log(this.icon);
	},
};
</script>
