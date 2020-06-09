<template>
	<a @click="catChange" class="panel-block" :class="activeLink">
		{{name}}
	</a>
</template>

<script>
export default {
	props: {
		value: Object,
		name: String,
	},
	methods: {
		catChange () {
			this.$emit('input');
		},
	},
	computed: {
		activeLink () {
			if (this.value.name === this.name) {
				return 'has-background-platinum has-text-light';
			}
			return 'has-background-light has-text-dark';
		},
	},
};
</script>
