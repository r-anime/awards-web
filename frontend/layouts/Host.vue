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
		<div class="breadcrumb-wrap has-background-light">
			<div class="breadcrumb">
				<ul>
					<router-link
						v-for="routeRecord in matchedRoutes"
						:key="routeRecord.path"
						tag="li"
						:to="routeRecord.path"
						exact-active-class="is-active"
					>
						<a>{{routeRecord.meta.title}}</a>
					</router-link>
				</ul>
			</div>
		</div>
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
	computed: {
		matchedRoutes () {
			return this.$route.matched.filter(route => route.meta && route.meta.title);
		},
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
</style>
