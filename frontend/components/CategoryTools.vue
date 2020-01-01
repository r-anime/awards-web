<template>
	<div class="section">
		<h2 class="title is-3">Category Tools</h2>
		<div class="buttons">
			<button class="button">Import entries from category</button>
			<button class="button">Import entries from text</button>
			<button class="button">Export entries to text</button>
		</div>
		<div class="buttons" v-if="category.entryType == 'themes'">
			<!--I really need to handle this better-->
			<button class="button is-primary" :class="{'is-loading' : submitting && submitType == 'op'}"
			:disabled="submitting && submitType != 'op'" @click="submitCreateThemes('op')">Import OPs</button>

			<button class="button is-primary" :class="{'is-loading' : submitting && submitType == 'ed'}"
			:disabled="submitting && submitType != 'ed'" @click="submitCreateThemes('ed')">Import EDs</button>

			<button class="button is-primary" :class="{'is-loading' : submitting && submitType == 'ost'}"
			:disabled="submitting && submitType != 'ost'" @click="submitCreateThemes('ost')">Import OSTs</button>

			<!--This too tbh-->

			<button class="button is-danger" :class="{'is-loading' : deleting && deleteType == 'op'}"
			:disabled="deleting && deleteType != 'op'" @click="submitDeleteThemes('op')">Delete OPs</button>

			<button class="button is-danger" :class="{'is-loading' : deleting && deleteType == 'ed'}"
			:disabled="deleting && deleteType != 'ed'" @click="submitDeleteThemes('ed')">Delete EDs</button>
			<button class="button is-danger" :class="{'is-loading' : deleting && deleteType == 'ost'}"
			:disabled="deleting && deleteType != 'ost'" @click="submitDeleteThemes('ost')">Delete OSTs</button>
			<h2 class="title is-6">Themes are only meant to be imported once. After importing, they will become available across all categories. Delete all OP/EDs/OSTs before re-importing to avoid duplicates.</h2>
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
			submitType: '',
			deleteType: '',
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
		...mapActions(['createThemes', 'deleteThemes']),
		disableButtons (type,action) {
			if (action === 'create') {
				switch (type) {
					case 'op':
						this.submitType = 'op';
						break;
					case 'ed':
						this.submitType = 'ed';
						break;
					case 'ost':
						this.submitType = 'ost';
						break;
				}
			} else if (action === 'delete') {
				switch (type) {
					case 'op':
						this.deleteType = 'op';
						break;
					case 'ed':
						this.deleteType = 'ed';
						break;
					case 'ost':
						this.deleteType = 'ost';
						break;
				}
			}
		},
		releaseButtons () {
			this.deleteType = '';
			this.submitType = '';
		},
		submitCreateThemes (type) {
			this.submitting = true;
			this.disableButtons(type,'create');
			setTimeout(async () => {
				try {
					await this.createThemes({data: {themeType: type}});
				} finally {
					this.submitting = false;
					this.releaseButtons();
				}
			});
		},
		submitDeleteThemes (type) {
			this.deleting = true;
			this.disableButtons(type,'delete');
			setTimeout(async () => {
				try {
					await this.deleteThemes(type);
				} finally {
					this.deleting = false;
					this.releaseButtons();
				}
			});
		},
	},
};
</script>
