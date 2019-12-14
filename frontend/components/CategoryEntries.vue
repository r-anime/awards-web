<template>
	<div>
		<show-picker
			v-if="category.entryType === 'shows'"
			v-model="selections"
		/>
		<char-picker
			v-else-if="category.entryType === 'characters'"
			v-model="selections"
		/>
		<VAPicker
			v-else-if="category.entryType === 'vas'"
			v-model="selections"
		/>
		<ThemePicker
			v-else-if="category.entryType === 'themes'"
			v-model="selections"
		/>
		<div class="submit-wrapper">
			<button
				class="button is-success"
				:class="{'is-loading': submitting}"
				@click="submit"
			>
				Update Entries
			</button>
		</div>
	</div>
</template>

<script>
import {mapActions} from 'vuex';
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
			selections: this.category.entries ? JSON.parse(this.category.entries) : [],
			submitting: false,
		};
	},
	methods: {
		...mapActions([
			'updateCategory',
		]),
		async submit () {
			this.submitting = true;
			try {
				await this.updateCategory({
					id: this.category.id,
					data: {
						entries: JSON.stringify(this.selections),
					},
				});
			} finally {
				this.submitting = false;
			}
		},
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
