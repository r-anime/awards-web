<template>
	<div>Placeholder for table component</div>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import Vuetable from 'vuetable-2';

export default {
	components: {
		Vuetable,
	},
	computed: {
		...mapState(['preferences']),
	},
	methods: {
		...mapActions(['getPreferences']),
	},
	async mounted () {
		await this.getPreferences();
		console.log(this.preferences);
	}
}
</script>