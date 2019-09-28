<template>
	<show-picker v-model="selections"/>
</template>

<script>
import {mapActions} from 'vuex';
import ShowPicker from './ShowPicker';

export default {
	components: {
		ShowPicker,
	},
	props: {
		category: Object,
	},
	data () {
		return {
			selections: [],
		};
	},
	methods: {
		...mapActions([
			// 'updateCategoryEntries', // TODO not implemented
		]),
	},
};
</script>
