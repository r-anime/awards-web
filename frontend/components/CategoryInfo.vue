<template>
	<div class="section">
		<h2 class="title is-3">Category Information</h2>
		<form @submit.prevent="submitEditCategory">
			<div class="field">
				<label class="label">Name</label>
				<div class="control">
					<input class="input" v-model="newCategoryName"/>
				</div>
				<p class="help">The name of the category</p>
			</div>
			<div class="field">
				<label class="label">Entry Type</label>
				<div class="control">
					<div class="select">
						<select v-model="newEntryType">
							<option disabled>Select one</option>
							<option value="PLACEHOLDER_SHOW">Shows</option>
							<option value="PLACEHOLDER_CHAR">Characters</option>
							<option value="PLACEHOLDER_PERF">VA Performances</option>
						</select>
					</div>
				</div>
				<p class="help">What type of entries can be included in this category</p>
			</div>
			<div class="field">
				<div class="control">
					<button
						type="submit"
						class="button is-primary"
						:class="{'is-loading': submitting}"
					>
						Save Changes
					</button>
				</div>
			</div>
		</form>
	</div>
</template>

<script>
export default {
	props: ['category'],
	data () {
		return {
			newCategoryName: this.category ? this.category.name : '',
			newEntryType: 'PLACEHOLDER_SHOW',
			submitting: false,
		};
	},
	methods: {
		submitEditCategory () {
			// When this gets implemented, make sure there's a warning when
			// changing the entry type, as that will also erase all current
			// entries.
			this.submitting = true;
			setTimeout(() => {
				alert('TODO');
				this.submitting = false;
			});
		},
	},
	watch: {
		category (newVal, oldVal) {
			// If the category was already defined, we don't want to update the
			// other data, because the user may be making changes
			if (oldVal) return;
			this.newCategoryName = newVal.name;
			this.newEntryType = 'PLACEHOLDER_SHOW';
		},
	},
};
</script>
