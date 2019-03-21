<template>
	<main>
		<h1>Apply to be a Juror!</h1>
		<template v-if="$root.user">

		</template>
		<template v-else>
			In order to apply to be a juror, you'll need to <a href="/auth/reddit">log in</a>.
		</template>
	</main>
</template>

<script>
export default {
	props: {},
	data () {
		return {
		};
	},
	methods: {
	},
};
</script>
