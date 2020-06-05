<template>
	<a @click="catChange" class="panel-block" :class="{'has-background-light has-text-dark': value.name === name}">
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
};
</script>
