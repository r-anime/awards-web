<template>
	<section class="hero is-fullheight-with-navbar has-background-periwinkle">
		<div class="hero-body">
			<div class="container ">
				<div class="columns is-centered">
					<div class="column is-5-tablet is-4-desktop is-3-widescreen">
						<img loading="lazy" :src="snooImage"/>
					</div>
					<div class="column is-5-tablet is-4-desktop is-3-widescreen">
						<div class="content has-text-centered has-text-light" v-if="me">
							<h3>Hello, /u/{{me.reddit.name}}</h3>
							<div class="buttons is-centered">
								<div v-if="me.level >= 2">
									<router-link to="/host">
										<button class="button is-primary is-large is-platinum">Host Dashboard</button>
									</router-link>
								</div>
								<div v-else class="content has-text-centered">
									<p>You don't seem to be a host. There's nothing here for you yet.</p>
								</div>
							</div>
						</div>
						<div class="content has-text-centered" v-else>
							<h3>Hello, you're not logged in</h3>
							<p>
								<a href="/auth/reddit/login" class="button is-platinum is-large">
									Log in with Reddit
								</a>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<footer class="hero-foot footer has-background-dperiwinkle has-text-light">
			<div class="content has-text-centered">
				<a href="/about" class="has-text-light">About/Credits</a>
			</div>
		</footer>
	</section>
</template>

<script>
import {mapState, mapGetters} from 'vuex';
import snoo from '../../../img/bannerSnooJump.png';

export default {
	data () {
		return {
			deleting: false,
			snooImage: snoo,
		};
	},
	computed: {
		...mapState([
			'me',
		]),
		...mapGetters([
			'accountOldEnough',
		]),
		info () {
			return `${JSON.stringify(this.me, null, 4)}`;
		},
	},
	methods: {
		async deleteVotes () {
			this.deleting = true;
			await fetch('/api/votes/all/delete', {
				method: 'GET',
			});
			this.deleting = false;
		},
	},
};
</script>
