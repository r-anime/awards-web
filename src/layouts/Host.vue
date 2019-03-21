<template>
	<body>
		<nav-bar
			:routes="[
				{name: 'Dashboard', path: '/host'},
				{name: 'Users', path: '/host/users'},
				{name: 'Public stuff', path: '/'},
			]"
			vertical
		/>
		<div class="content-wrap">
			<router-view/>
		</div>
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

<style lang="scss" scoped>
body {
	margin: 0;
	font-family: sans-serif;
	display: flex;
	overflow: hidden;
	height: 100vh;
}
.nav-bar {
	flex: 0 0 200px;
	order: 1;
}
.content-wrap {
	flex: 0 1 100%;
	overflow: auto;
}
</style>
