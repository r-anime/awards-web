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
					<div class="buttons">
					<div v-if="me.level >= 2">
						<router-link to="/host">
							<button class="button is-primary is-large">Host Dashboard</button>
						</router-link>.
					</div>
					<div>
						<router-link to="/vote">
							<button class="button is-primary is-large">Click here to vote</button>
						</router-link>
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
import {mapState} from 'vuex';
import snoo from '../../img/bannerSnooJump.png';

export default {
	computed: {
		...mapState([
			'me',
		]),
		info () {
			return `${JSON.stringify(this.me, null, 4)}`;
		},
		snooImage () {
			return snoo;
		},
	},
};
</script>
