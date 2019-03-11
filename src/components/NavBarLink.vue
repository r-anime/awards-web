<template>
	<span
		:class="['nav-bar-link', {'nav-bar-dropdown': namedChildren}]"
	>
		<span v-if="$slots.default" class="nav-bar-link-inner">
			<slot/>
		</span>
		<router-link v-if="route" :to="route.path" :exact="!namedChildren">
			{{route.name}}
		</router-link>
		<span v-if="namedChildren && namedChildren.length" class="nav-bar-dropdown">
			<router-link
				v-for="childRoute in namedChildren"
				:key="childRoute.path"
				:to="childRoute.path"
			>
				{{childRoute.name}}
			</router-link>
		</span>
		<span v-else-if="$slots.dropdown" class="nav-bar-dropdown">
			<slot name="dropdown"/>
		</span>
	</span>
</template>

<script>
export default {
	props: {
		route: Object,
	},
	computed: {
		namedChildren () {
			return this.route && this.route.children && this.route.children.filter(route => route.name);
		},
	},
};
</script>

<style lang="scss">
.nav-bar-link {
	display: block;
	margin: 0;
	height: 50px;
	line-height: 50px;
	color: inherit;
	text-decoration: inherit;

	> a,
	> span  {
		display: block;
		padding: 0 10px;
	}
	&:hover > a,
	&:hover > span {
		background: #222;
	}
	.router-link-active {
		color: red;
	}

	.nav-bar-dropdown {
		display: none;
	}

	&:hover .nav-bar-dropdown {
		display: block;
	}
}
</style>
