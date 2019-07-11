<template>
	<body>
		<nav-bar
			:routes="[
				{name: 'Categories', path: '/host/categories'},
				{name: 'Users', path: '/host/users'},
			]"
			class="is-dark"
			fullwidth
		/>
		<keep-alive>
			<router-view/>
		</keep-alive>
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
</style>
