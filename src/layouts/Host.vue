<template>
	<body>
		<nav-bar :routes="routes" :user="$root.user" vertical/>
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
			routes: [

			],
		};
	},
};
</script>
