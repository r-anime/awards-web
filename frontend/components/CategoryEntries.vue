<template>
	<div>
		<show-picker
			v-if="category.entryType === 'shows'"
			v-model="selections"
		/>
		<h2 class="title" v-else>
			This is a non-show category, editing is not yet supported
		</h2>

		<hr>
		<button
			class="button is-success"
			:class="{'is-loading': submitting}"
			@click="submit"
		>
			Update Entries
		</button>
	</div>
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
			selections: JSON.parse(this.category.entries),
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
	watch: {
		category (newVal, oldVal) {
			console.log('category changed', newVal, oldVal);
			// If the category was already defined, we don't want to update the
			// other data, because the user may be making changes
			if (oldVal) return;
			this.selections = JSON.parse(newVal.entries);
		},
	},

};
</script>
