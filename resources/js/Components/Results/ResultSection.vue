<!-- TODO: FA icons  -->
<template>
	<div :id="slug" class="awardSectionContainer py-6">
		<div class="awardSectionHeader has-text-centered has-text-light">
			<div class="is-flex is-justify-content-center is-align-items-center">
				<div class="sectionIconContainer">
                    <!-- TODO: FA Icons -->
					<!-- <fa-icon :icon="icon" size="7x" class="has-text-gold mb-20 mt-20" /> -->
				</div>
				<div class="title-container">
					<h2 class="is-size-1 title has-text-light">{{sectionName}} Awards</h2>
				</div>
			</div>
            <!-- ! Old code, Not sure what blurbs are  -->
			<!-- <div
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
			</div> -->
		</div>
		<result-category
			v-for="(category, index) in section"
			:key="index"
			:category="category"
		/>
        <!--         
			@nomModal="emitNomModal"
			@hmModal="emitHMModal"
			@catModal="emitCatModal"
         -->
	</div>
</template>

<script setup>
import ResultCategory from './ResultCategory.vue';
import { computed } from 'vue';
const props = defineProps({ section: Array });

// ? Are there any drawbacks to using non-computed constants?
const sectionName = props.section[0].type[0].toUpperCase()
	+ props.section[0].type.substr(1);

/*
const sectionName = computed(() => {
    const name = props.section[0].type;
    return name[0].toUpperCase() + name.substr(1);
});
*/

const icon = computed(() => {
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
});
</script>