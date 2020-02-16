<template>
    <div :id="slug" class="awardSectionContainer">
        <div class="awardSectionHeader">
            <div class="sectionIconContainer"><img class="sectionIcon" :alt="section.name" :src="section.icon" /></div>
            <h1 class="sectionHeader">{{section.name}} Awards</h1>
            <div
                v-if="typeof section.blurb === 'string'"
                class="awardSectionBlurb"
                v-html="util.getMarkDown(section.blurb)"
            />
            <div
                v-else
                v-for="(blurb, index) in section.blurb"
                class="awardSectionBlurb"
                :key="index"
                v-html="util.getMarkDown(blurb)"
            />
        </div>
        <awards-category
            v-for="award in section.awards"
            :key="award.name"
            :award="award"
        />
    </div>
</template>

<script>
const util = require('../util');

export default {
	props: ['section'],
	computed: {
		slug () {
			return `section-${util.slugify(this.section.name)}`;
		},
	},
};
</script>
