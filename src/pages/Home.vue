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
			return `${JSON.stringify(this.$root.user, null, 4)}\n\n${JSON.stringify(this.$root.redditor, null, 4)}`;
		},
	},
};
</script>
