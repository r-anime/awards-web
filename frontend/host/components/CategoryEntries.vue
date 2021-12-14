<template>
	<div v-if="loaded">
		<show-picker
			v-if="category.entryType === 'shows'"
			v-model="computedEntries"
			:category="category"
		/>
		<char-picker
			v-else-if="category.entryType === 'characters'"
			v-model="computedEntries"
			:category="category"
		/>
		<VAPicker
			v-else-if="category.entryType === 'vas'"
			v-model="computedEntries"
			:category="category"
		/>
		<ThemePicker
			v-else-if="category.entryType === 'themes'"
			v-model="computedEntries"
			:category="category"
		/>
	</div>
</template>

<script>
import {mapActions, mapState} from 'vuex';
import ShowPicker from './EntryLayouts/ShowPicker';
import CharPicker from './EntryLayouts/CharPicker';
import VAPicker from './EntryLayouts/VAPicker';
import ThemePicker from './EntryLayouts/ThemePicker';

export default {
	components: {
		ShowPicker,
		CharPicker,
		VAPicker,
		ThemePicker,
	},
	props: {
		category: Object,
	},
	data () {
		return {
			computedEntries: null,
			loaded: false,
		};
	},
	computed: {
		...mapState([
			'entries',
		]),
	},
	methods: {
		...mapActions([
			'getEntries',
		]),
	},
	async mounted () {
		if (!this.entries) {
			await this.getEntries();
		}
		this.computedEntries = this.entries.filter(entry => entry.categoryId === this.category.id);
		this.loaded = true;
	},
};
</script>
