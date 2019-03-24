<template>
	<div>
		<h3>yeet</h3>
		<pre>{{info}}</pre>
	</div>
</template>

<script>
export default {
	computed: {
		info () {
			return `${JSON.stringify(this.$root.me, null, 4)}`;
		},
	},
};
</script>
