<template>
	<body>
		<nav-bar
			:routes="[
				{name: 'Categories', path: '/host/categories'},
				{name: 'Users', path: '/host/users'},
				{name: 'Results Summary', path: '/host/results'}
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
		<div class="breadcrumb-wrap has-background-light">
			<div class="breadcrumb">
				<ul>
					<host-breadcrumb-link
						v-for="routeRecord in matchedRoutes"
						:key="routeRecord.path"
						:route="routeRecord"
					/>
				</ul>
			</div>
		</div>
		<div class="full-height-content">
			<router-view/>
		</div>
	</body>
</template>

<script>
import NavBar from '../components/NavBarSimple.vue';
import HostBreadcrumbLink from '../components/HostBreadcrumbLink';

export default {
	components: {
		NavBar,
		HostBreadcrumbLink,
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
.full-height-content {
	height: calc(100vh - 6.25rem);
	overflow: auto;
	@include mobile {
		height: auto;
	}
}
</style>
