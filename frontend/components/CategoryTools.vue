<template>
	<div class="section">
		<h2 class="title is-3">Category Tools</h2>
		<div class="buttons">
			<button class="button">Import entries from category</button>
			<button class="button">Import entries from text</button>
			<button class="button">Export entries to text</button>
		</div>
		<div class="buttons" v-if="category.entryType == 'themes'">
			<button class="button is-primary" v-bind:class="{'is-loading' : submitting}" @click="submitCreateThemes('op')">Import OPs</button>
			<button class="button is-primary" @click="submitCreateThemes('ed')" disabled>Import EDs</button>
			<button class="button is-primary" disabled>Import OSTs</button>
			<button class="button is-danger" v-bind:class="{'is-loading' : deleting}" @click="submitDeleteThemes('op')">Delete OPs</button>
			<button class="button is-danger" @click="submitDeleteThemes('ed')" disabled>Delete EDs</button>
			<button class="button is-danger" @click="submitDeleteThemes('ost')" disabled>Delete OSTs</button>
			<h2 class="title is-6">OPs and EDs are only meant to be imported once. After importing, they will become available across all categories. Delete all OP/EDs before re-importing to avoid duplicates.</h2>
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
			deleting: false,
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
		...mapActions(['createThemes','deleteThemes']),
		submitCreateThemes (type) {
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
		submitDeleteThemes (type) {
			this.deleting = true;
			setTimeout(async () => {
				try {
					await this.deleteThemes({data: {themeType: type}});
				}
				finally {
					this.deleting = false;
				}
			});
		},
	},
};
</script>
