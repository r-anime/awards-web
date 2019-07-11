<template>
	<router-link
		v-if="route && !hasDropdown"
		:to="route.path"
		:exact="false"
		:class="cssClass"
		active-class="is-active"
	>
		{{route.name}}
	</router-link>
	<span v-else :class="cssClass">
		<span v-if="$slots.default" class="navbar-link">
			<slot/>
		</span>

		<router-link
			v-if="route"
			:to="route.path"
			:exact="!namedChildren"
			class="navbar-link"
			active-class="is-active"
		>
			{{route.name}}
		</router-link>

		<span
			v-if="namedChildren && namedChildren.length"
			class="navbar-dropdown"
		>
			<router-link
				v-for="childRoute in namedChildren"
				:key="childRoute.path"
				:to="childRoute.path"
			>
				{{childRoute.name}}
			</router-link>
		</span>
		<span
			v-else-if="$slots.dropdown"
			class="navbar-dropdown"
		>
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
		hasDropdown () {
			return this.namedChildren && this.namedChildren.length || this.$slots.dropdown;
		},
		cssClass () {
			return {
				'navbar-item': true,
				'has-dropdown': this.hasDropdown,
				'is-hoverable': this.hasDropdown,
			};
		},
	},
};
</script>

<style lang="scss">
// $height: 50px;
// .nav-bar-link {
// 	display: block;
// 	position: relative;
// 	margin: 0;
// 	height: $height;
// 	line-height: $height;
// 	color: inherit;
// 	text-decoration: inherit;

// 	&:hover {
// 		background: #222;
// 	}

// 	> a,
// 	> span  {
// 		display: block;
// 		padding: 0 10px;
// 	}
// 	.router-link-active {
// 		color: red;
// 	}

// 	.nav-bar-dropdown {
// 		right: 0;
// 		height: 0;
// 		display: flex;
// 		flex-direction: column;
// 		align-items: stretch;
// 		overflow: hidden;
// 	}

// 	&:hover .nav-bar-dropdown {
// 		height: auto;
// 		background: inherit;
// 	}

// 	.vertical & {
// 		height: auto;
// 		line-height: 1.5;
// 		padding-top: 10px;
// 		padding-bottom: 10px;

// 		.nav-bar-dropdown {
// 			height: auto;
// 		}
// 	}
// }
</style>
