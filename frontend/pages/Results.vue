<template>
	<section class="section has-background-dperiwinkle">
		<awards-section
            v-for="(section, index) in results.sections"
            :key="index"
            :section="section"
            >
        </awards-section>
	</section>
</template>

<script>
/* eslint-disable no-unused-vars */
import data2018 from '../../public/data/results2018.json';
import data2019 from '../../public/data/results2019.json';
import AwardsSection from '../results/ResultSection';

export default {
	components: {
		AwardsSection,
	},
	data () {
		return {
			results: data2019,
		};
	},
	mounted () {
		console.log(this.results);
	},
};
</script>
