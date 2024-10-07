<template>
	<section class="section has-background-dark pb-100 pt-100">
		<div class="container application-container pt-30 pb-30 mt-0 mb-0">
			<div class="is-centered">
				<img :src="logo" class="image" style="height: 96px; margin: 0 auto;"/>
			</div>
			<h1 class="title is-3 has-text-light has-text-centered mb-50">Juror Application</h1>
			<div class="message is-lperiwinkle">
				<div class="message-header">
					Application Submitted
				</div>
				<div class="message-body">
					<div class="pl-5 pr-5">
						Thank you for applying for the r/anime awards! You may review and change your application any time before the <b>23rd of October</b> by navigating back to the application page. You will be contacted on Reddit the <b>3rd of November</b> about the outcome.
						<br><br>
						Once again, thank you for applying and we hope to see you soon!
					</div>
					<br>
					<br>
					<div class="has-text-centered">
						<router-link to="/apps" class="button is-primary">Back to Application</router-link>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>

<script>
import 'codemirror/lib/codemirror.css';
import {mapState, mapActions} from 'vuex';
import logo2022 from '../../../img/awards2024.png';

export default {
	computed: {
		...mapState([
			'locks',
		]),
	},
	data () {
		return {
			logo: logo2022,
		};
	},
	methods: {
		...mapActions(['getApplicant', 'getLocks', 'getMe']),
	},
};
</script>
