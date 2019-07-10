<template>
	<section class="section">
		<div class="container">
			<h2 class="is-size-2">
				Accounts
			</h2>
			<h3 class="is-size-3">
				Reddit
			</h3>
			<div class="media">
				<figure class="media-left">
					<p class="image is-64x64">
						<img :src="$root.me.reddit.avatar"/>
					</p>
				</figure>
				<div class="media-content">
					<p class="is-size-4">
						You are <a :href="redditLink" target="_blank">/u/{{$root.me.reddit.name}}</a>
					</p>
					<p>
						Your Reddit account is the main way we identify you, so you can't unlink it.
					</p>
				</div>
			</div>

			<h3 class="is-size-3">
				Discord
			</h3>
		</div>
	</section>
</template>

<script>
export default {
	computed: {
		redditLink () {
			return `https://www.reddit.com/user/${this.$root.me.reddit.name}`;
		},
	},
};
</script>

<style lang="sass">

</style>
