<template>
	<show-picker v-model="selections"/>
</template>

<script>
import ShowPicker from './ShowPicker';

export default {
	components: {
		ShowPicker,
	},
	data () {
		return {
			selections: [],
		};
	},
};
</script>
