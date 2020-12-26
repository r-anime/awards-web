<template>
	<body>
		<div v-if="loaded">
			<nav-bar
				:routes="[{name: 'Final Voting', path: '/final-vote'}]"
				class="is-dperiwinkle has-periwinkle-underline"
				finalVote
			>
				<template v-slot:title>
					<router-link to="/" style="color: inherit;">
						<h1 class="is-size-4">/r/anime Awards</h1>
					</router-link>
				</template>
			</nav-bar>
			<router-view/>
			<footer class="hero-foot footer has-background-dperiwinkle has-text-light">
				<div class="container has-text-centered">
					<small class="has-text-light">
						The r/anime Awards are a rag tag group of volunteers passionate about showcasing the shows we love. As such, we do not own or claim ownership of
						any of the shows, characters, or images showcased on this site.
						All copyrights and/or trademarks of any character and/or image used belong to their respective owner(s). Please don't sue us.
					</small>
					<br>
					<br>
					<br>
					<a href="/about" class="footerLink has-text-light">About/Credits</a>
				</div>
			</footer>
		</div>
	</body>
</template>

<script>
import NavBar from '../../common/NavBarSimple';
import {mapState, mapActions} from 'vuex';
export default {
	components: {
		NavBar,
	},
	data () {
		return {
			loaded: false,
		};
	},
	computed: {
		...mapState(['me']),
	},
	methods: {
		...mapActions(['getMe']),
	},
	async mounted () {
		if (!this.me) {
			await this.getMe();
		}
		this.loaded = true;
	},
};
</script>
