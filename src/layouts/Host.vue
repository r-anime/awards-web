<template>
	<body>
		<nav-bar
			:routes="[
				{name: 'Back to public', path: '/'}
			]"
			class="is-dark"
			fullwidth
		/>
		<div class="content-wrap">
			<aside class="menu">
				<template v-for="(routes, label) in nav">
				<h3 class="menu-label has-text-light" :key="label">
					{{label}}
				</h3>
				<ul class="menu-list" :key="label">
					<li v-for="route in routes" :key="label + route.path">
						<router-link
							:to="route.path"
							class="has-text-light"
							active-class="is-active"
							exact
						>
							{{route.name}}
						</router-link>
					</li>
				</ul>
				</template>
			</aside>
			<main>
				<keep-alive>
					<router-view/>
				</keep-alive>
			</main>
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
			nav: {
				'Host Tools': [
					{name: 'Dashboard', path: '/host'},
					{name: 'Users', path: '/host/users'},
				],
			},
		};
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
.content-wrap {
	flex: 0 1 100%;
	display: flex;
	overflow: hidden;
	> .menu {
		flex: 0 0 200px;
		order: -1;
		padding: 1.5rem 0.75rem;
		background: hsl(0, 0%, 29%);
		a.is-active {
			background: $primary;
		}
		a:hover:not(.is-active) {
			background: hsl(0, 0%, 48%);
		}
	}
	> main {
		flex: 0 1 100%;
		overflow: auto;
	}
}
</style>
