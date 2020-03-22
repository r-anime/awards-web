<template>
	<body>
			<nav-bar
				:routes="[
					{name: 'Genre', path: '/results/genre'},
					{name: 'Character', path: '/results/character'},
					{name: 'Production', path: '/results/production'},
					{name: 'Main', path: '/results/main'},
					{name: 'Test', path: '/results/test'},
					{name: 'View All', path: '/results/all'},
					{name: 'Extra', path: '/extra'},
					{name: 'Archive', path: '/archive'},
					{name: 'Acknowledgements', path: '/acknowledgements'},
					{name: 'About/Credits', path: '/about'},
				]"
				class="is-dperiwinkle has-periwinkle-underline"
			>
			<template v-slot:title>
				<router-link to="/" style="color: inherit;">
					<h1 class="is-size-4">/r/anime Awards</h1>
				</router-link>
				<span class="tag is-small" style="margin-left:0.8rem;">Host Dashboard</span>
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
	</body>
</template>

<script>
import NavBar from '../components/NavBar.vue';

export default {
	components: {
		NavBar,
	},
};
</script>

<style lang="scss" scoped>
@use "../styles/main.scss" as *;

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
