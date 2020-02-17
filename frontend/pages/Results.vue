<template>
	<section class="hero is-fullheight-with-navbar">
		<awards-section
            v-for="(section, index) in results.sections"
            :key="index"
            :section="section"
            >
        </awards-section>
	</section>
</template>

<script>
import data2018 from '../../public/data/results2018.json';
import AwardsSection from '../results/ResultSection';

export default {
	components: {
		AwardsSection,
	},
	data () {
		return {
			results: data2018,
		};
	},
	mounted () {
		console.log(this.results);
	},
};
</script>
