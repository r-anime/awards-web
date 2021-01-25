<template>
	<section class="section has-background-dark pb-100 pt-100">
		<div class="container application-container pt-30 pb-30 mt-0 mb-0">
			<div class="is-centered">
				<img :src="logo" class="image" style="height: 96px; margin: 0 auto;"/>
			</div>
			<h1 class="title is-3 has-text-light has-text-centered mb-50">Thanks for your votes!</h1>
			<div class="message is-lperiwinkle">
				<div class="message-body">
					<div class="pl-5 pr-5">
						Thanks for voting! We plan on hosting a results livestream towards the end of February, so stay tuned and check <a href="https://www.reddit.com/r/anime/">r/anime</a> for more information!
					</div>
					<br>
					<br>
					<div class="has-text-centered">
						<router-link to="/final-vote" class="button is-primary">Change My Votes</router-link>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>

<script>
import 'codemirror/lib/codemirror.css';
import {mapState, mapActions} from 'vuex';
import logo2020 from '../../../img/awards2020.png';

export default {
	computed: {
		...mapState([
			'locks',
		]),
	},
	data () {
		return {
			logo: logo2020,
		};
	},
	methods: {
		...mapActions(['getApplicant', 'getLocks', 'getMe']),
	},
};
</script>
