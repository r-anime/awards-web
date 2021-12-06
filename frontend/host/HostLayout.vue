<template>
	<body>
		<nav-bar
			:routes="routes"
			class="is-llperiwinkle"
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
import {mapState} from 'vuex';
import NavBar from '../common/NavBarSimple.vue';
import HostBreadcrumbLink from './components/HostBreadcrumbLink';

export default {
	components: {
		NavBar,
		HostBreadcrumbLink,
	},
	computed: {
		...mapState([
			'me',
		]),
		matchedRoutes () {
			return this.$route.matched.filter(route => route.meta && route.meta.title);
		},
		routes () {
			return [
				{name: 'Categories', path: '/host/categories'},
				this.me && this.me.level >= 4 && {name: 'Admin Panel', path: '/host/admin'},
				{name: 'Users', path: '/host/users', children: [
					{name: 'Applications', path: '/host/applications'},
					{name: 'Allocations', path: '/host/allocations'}
				]},
				{name: 'Results', path: '#', children: [
					{name: 'Nominations', path: '/host/results'},
					{name: 'Final', path: '/host/final-results'}
				]},
				{name: 'Entries', path: '/host/entries'}
				,
			].filter(s => s);
		},
	},
};
</script>

<style lang="scss" scoped>
@use "../styles/utilities" as *;

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
