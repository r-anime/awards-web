<template>
	<body>
		<nav-bar
			:routes="[
				{name: 'About/Credits', path: '/about'},
			]"
			class="is-llperiwinkle"
			fullwidth
		>
			<template v-slot:title>
				<router-link to="/" style="color: inherit;">
					<h1 class="is-size-4">/r/anime Awards</h1>
				</router-link>
				<span class="tag is-small" style="margin-left:0.8rem;">Host Dashboard</span>
			</template>
		</nav-bar>
		<router-view/>
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
