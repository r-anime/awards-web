<template>
	<body>
		<nav-bar class="is-llperiwinkle" :routes="routes">
			<template v-slot:title>
				<router-link to="/" style="color:inherit">
					<h1 class="is-size-4">/r/anime Awards</h1>
				</router-link>
			</template>
		</nav-bar>
		<router-view/>
	</body>
</template>

<script>
import {mapState} from 'vuex';
import NavBar from '../common/NavBarSimple.vue';
export default {
	components: {
		NavBar,
	},
	computed: {
		...mapState([
			'me',
		]),
		routes () {
			return [
				this.me && this.me.level >= 2 && {name: 'Host Dashboard', path: '/host'},
				{name: 'About/Credits', path: '/about'},
			].filter(s => s);
		},
	},
};
</script>

<style lang="scss" scoped>
body {
	margin: 0;
	font-family: sans-serif;
	height: calc(100vh - 186px);
}
</style>
