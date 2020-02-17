<template>
    <div :id="slug" class="awardSectionContainer">
        <div class="awardSectionHeader">
            <div class="sectionIconContainer"><img class="sectionIcon" :alt="section.name" :src="section.icon" /></div>
            <h1 class="sectionHeader">{{section.name}} Awards</h1>
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
	},
};
</script>
