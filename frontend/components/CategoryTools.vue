<template>
	<div class="section">
		<h2 class="title is-3">Category Tools</h2>
		<div class="buttons">
			<button class="button">Import entries from category</button>
			<button class="button">Import entries from text</button>
			<button class="button">Export entries to text</button>
		</div>
	</div>
</template>

<script>
export default {
	props: ['category'],
	watch: {
		// Update displayed information when the store gets updated
		category (newVal, oldVal) {
			// If the category was already defined, we don't want to update the
			// other data, because the user may be making changes
			if (oldVal) return;
			// Update relevant properties here
		},
	},
};
</script>
