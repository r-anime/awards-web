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
		<div class="submit-wrapper">
			<button
				class="button is-success"
				:class="{'is-loading': submitting}"
				@click="submit"
			>
				Update Entries
			</button>

			<button
				class="button is-primary"
				@click="selectAll"
			>
				Select All
			</button>

			<button
				class="button is-primary"
				@click="unselectAll"
			>
				Unselect All
			</button>
		</div>
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
			submitting: false,
			computedEntries: null,
			loaded: false,
		};
	},
	computed: {
		...mapState(['entries']),
	},
	methods: {
		...mapActions([
			'getEntries',
			'updateEntries',
		]),
		async submit () {
			this.submitting = true;
			try {
				await this.updateEntries({
					id: this.category.id,
					entries: this.computedEntries,
				});
			} finally {
				this.submitting = false;
			}
		},
		selectAll () {
			const entries = document.querySelectorAll('.item-picker-entry-cb');

			for (let i = 0; i < entries.length; ++i) {
				entries[i].checked = true;
			}
		},
		unselectAll () {
			const entries = document.querySelectorAll('.item-picker-entry-cb');

			for (let i = 0; i < entries.length; ++i) {
				entries[i].checked = false;
			}
		},
	},
	async mounted () {
		await this.getEntries(this.category.id);
		if (this.entries == null) {
			this.computedEntries = [];
		} else {
			this.computedEntries = this.entries;
		}
		this.loaded = true;
	},
};
</script>

<style lang="scss">
.submit-wrapper {
	box-shadow: inset 0 1px #dbdbdb;
	text-align: center;
	padding: 5px;
}
</style>
