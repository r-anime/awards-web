<template>
	<section class="section">
		<div class="container">
			<h2 class="title is-2">
				Account Information
			</h2>
			<div class="media">
				<figure class="media-left">
					<p class="image is-64x64">
						<img :src="me.reddit.avatar"/>
					</p>
				</figure>
				<div class="media-content">
					<p class="is-size-4">
						You are <a :href="redditLink" target="_blank">/u/{{me.reddit.name}}</a>
					</p>
					<p>
						To use this site, you grant us access to verify your Reddit account name and public profile information, such as avatar and account age.
					</p>
				</div>
			</div>
			<br> <!-- geo pls -->
			<h2 class="title is-2">
				Account Actions
			</h2>
			<div class="field">
				<div class="control">
					<button
						class="button is-danger"
						@click="deleteAccount"
					>
						Delete My /r/anime Awards Data
					</button>
				</div>
				<p class="help">
					This will delete your responses, votes and all other information associated with your Reddit account. It will not delete your Reddit account itself. <b>There is no further confirmation</b>; only click this button if you mean it.
				</p>
			</div>
		</div>
	</section>
</template>

<script>
import {mapState} from 'vuex';

export default {
	computed: {
		...mapState([
			'me',
		]),
		redditLink () {
			return `https://www.reddit.com/user/${this.me.reddit.name}`;
		},
	},
	methods: {
		deleteAccount () {
			fetch('/api/user/deleteaccount', {
				method: 'POST',
			}).then(() => {
				window.location.pathname = '/';
			});
		},
	},
};
</script>

<style lang="sass">

</style>
