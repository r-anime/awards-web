<template>
	<body>
		<nav-bar class="is-light" :routes="routes" />
		<router-view/>
	</body>
</template>

<script>
import NavBar from '../components/NavBar.vue';
export default {
	components: {
		NavBar,
	},
	computed: {
		routes () {
			const routes = [];
			if (this.$root.me && this.$root.me.level >= 2) {
				routes.push({name: 'Host page', path: '/host'});
			}
			return routes;
		},
	},
};
</script>

<style lang="scss" scoped>
body {
	margin: 0;
	font-family: sans-serif;
}
</style>
