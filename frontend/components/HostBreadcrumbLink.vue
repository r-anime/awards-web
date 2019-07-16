<template>
	<router-link
		tag="li"
		:to="location"
		active-class="is-active"
		exact
	>
		<a>{{title}}</a>
	</router-link>
</template>

<script>
export default {
	props: {
		route: Object,
	},
	computed: {
		title () {
			if (typeof this.route.meta.title === 'function') {
				return this.route.meta.title(this);
			}
			return this.route.meta.title;
		},
		location () {
			// TODO: This should be brought closer to however vue-router
			//       actually implements it.
			return this.route.path.replace(/:([a-z0-9_]+)/gi, (_, param) => this.$route.params[param]);
		},
	},
};
</script>
