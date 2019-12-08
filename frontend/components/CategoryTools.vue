<template>
	<div class="section">
		<h2 class="title is-3">Category Tools</h2>
		<div class="buttons">
			<button class="button">Import entries from category</button>
			<button class="button">Import entries from text</button>
			<button class="button">Export entries to text</button>
			<button class="button" v-bind:class="{'is-loading' : submitting}" @click="submitCreateThemes('op')" v-if="category.entryType == 'themes'">Import OPs</button>
			<button class="button" v-bind:class="{'is-loading' : submitting}" @click="submitCreateThemes('ed')" v-if="category.entryType == 'themes'" disabled>Import EDs</button>
			<button class="button" v-if="category.entryType == 'themes'" disabled>Import OSTs</button>
			<h2 class="title is-6">OPs and EDs are only meant to be imported once. After importing, they will become available across all categories. Deleting all OP/ED entries is not yet supported.</h2>
		</div>
	</div>
</template>

<script>
import {mapActions} from 'vuex';
export default {
	props: ['category'],
	data () {
		return {
			submitting: false,
		}
	},
	watch: {
		// Update displayed information when the store gets updated
		category (newVal, oldVal) {
			// If the category was already defined, we don't want to update the
			// other data, because the user may be making changes
			if (oldVal) return;
			// Update relevant properties here
		},
	},
	methods: {
		...mapActions(['createThemes']),
		submitCreateThemes(type) {
			this.submitting = true;
			setTimeout(async () => {
				try {
					await this.createThemes({data: {themeType: type}});
				}
				finally {
					this.submitting = false;
				}
			});
		},
	},
};
</script>
