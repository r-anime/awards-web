<template>
	<body>
		<div v-if="loaded">
			<nav-bar
				:routes="routes"
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
import NavBar from '../common/NavBar.vue';
import {mapState, mapActions} from 'vuex';
const navbarRoutes = require('../common/navbar-routes');

export default {
	components: {
		NavBar,
	},
	data () {
		return {
			routes: null,
			loaded: false,
		};
	},
	computed: {
		...mapState(['locks']),
	},
	methods: {
		...mapActions(['getLocks']),
	},
	async mounted () {
		if (!this.locks) {
			await this.getLocks();
		}
		const allocLock = this.locks.find(lock => lock.name === 'allocations');
		const guideLock = this.locks.find(lock => lock.name === 'juryGuide');
		const ongoingLock = this.locks.find(lock => lock.name === 'awards-ongoing');
		const appsOpen = this.locks.find(lock => lock.name === 'apps-open');
		if (ongoingLock.flag) {
			this.routes = navbarRoutes.ongoingRoutes;
		} else {
			this.routes = navbarRoutes.resultsRoutes;
		}
		if (appsOpen.flag) {
			this.routes.unshift({name: 'Jury Applications', path: '/apps'});
		}
		if (guideLock.flag) {
			this.routes.unshift({name: 'Jury Guide', path: '/juryguide'});
		}
		if (allocLock.flag) {
			this.routes.unshift({name: 'Allocations', path: '/allocations'});
		}
		this.routes.push({name: 'Profile', path: '/profile'});
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
