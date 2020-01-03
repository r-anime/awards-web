<template>
	<section class="hero is-fullheight-with-navbar">
		<div class="hero-body">
			<div class="container">
				<div class="columns is-centered">
					<div class="column is-5-tablet is-4-desktop is-3-widescreen">
						<img :src="snooImage"/>
					</div>
					<div class="column is-5-tablet is-4-desktop is-3-widescreen">
						<div class="content has-text-centered" v-if="me">
							<h3>Hello, /u/{{me.reddit.name}}</h3>
							<div class="buttons is-centered">
								<div v-if="me.level >= 2">
									<router-link to="/host">
										<button class="button is-primary is-large">Host Dashboard</button>
									</router-link>.
								</div>
								<div v-if="accountOldEnough">
									<router-link to="/instructions">
										<button class="button is-primary is-large">Vote Here!</button>
									</router-link>
								</div>
								<div v-else class="notification is-danger">
									Your Reddit account is too new to cast votes for the awards. Accounts must have been created before the beginning of the vote.<br>
									<strong>If you're signed in with the wrong account,</strong> you can sign out from the user menu in the top right. Sign into the correct account on Reddit before trying to sign into this site again.
								</div>
								<div v-if="me.level >= 4">
									<button @click="deleteVotes" class="button is-danger is-large"
									:class="{'is-loading': deleting}">Delete All Votes</button>
								</div>
							</div>
						</div>
						<div class="content has-text-centered" v-else>
							<h3>Hello, you're not logged in</h3>
							<p>
								<a href="/auth/reddit" class="button is-primary is-large">
									Log in with Reddit
								</a>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>

<script>
import {mapState, mapGetters} from 'vuex';
import snoo from '../../img/bannerSnooJump.png';

export default {
	data () {
		return {
			deleting: false,
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
		snooImage () {
			return snoo;
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
