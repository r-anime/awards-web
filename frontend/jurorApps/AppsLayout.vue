<template>
	<body>
		<div v-if="loaded">
			<nav-bar
				:routes="routes"
				:fullwidth="true"
				:apps="true"
				class="is-dperiwinkle has-periwinkle-underline"
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
					<router-link to="/about" style="color:inherit">
						<a class="footerLink has-text-light">About/Credits</a>
					</router-link>
				</div>
			</footer>
		</div>
	</body>
</template>

<script>
import NavBar from '../common/NavBarSimple';
import {mapState, mapActions} from 'vuex';

export default {
	components: {
		NavBar,
	},
	data () {
		return {
			routes: [{name: 'Jury Applications', path: '/apps'}],
			loaded: false,
		};
	},
	computed: {
		...mapState(['locks', 'me']),
	},
	methods: {
		...mapActions(['getLocks', 'getMe']),
	},
	async mounted () {
		if (!this.locks) {
			await this.getLocks();
		}
		if (!this.me) {
			await this.getMe();
		}
		this.loaded = true;
	},
};
</script>

<style lang="scss" scoped>
@use "../styles/utilities.scss" as *;

body {
	height: 100vh;
	display: flex;
	flex-direction: column;
}
.navbar {
	flex: 0 0 auto;
}
.breadcrumb-wrap {
	padding: 0.75rem;
}
.full-height-content {
	height: calc(100vh - 6.25rem);
	overflow: auto;
	@include mobile {
		height: auto;
	}
}
</style>
