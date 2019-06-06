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
	data () {
		return {
			routes: [],
		};
	},
};
</script>

<style lang="scss" scoped>
body {
	margin: 0;
	font-family: sans-serif;
}
</style>
