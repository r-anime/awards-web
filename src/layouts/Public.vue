<template>
	<body>
		<nav-bar :routes="[]" :user="$root.user"/>
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

<style lang="scss">
body {
	margin: 0;
	font-family: sans-serif;
}
</style>
