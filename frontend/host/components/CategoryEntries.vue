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
		<div class="field">
			<label class="label">Copy Entries:</label>
			<div class="field has-addons">
				
				<div class="control is-extended">
					<div class="select">
						<select v-model="copyId">
							<option v-for="(cat, index) in this.categories" :key="index" :value="cat.id">{{cat.name}}</option>
						</select>
					</div>
				</div>
				<div class="control">
					<button class="button is-primary" :class="{'is-loading' : submitting}"
					:disabled="submitting" @click="submitCopyEntries">Copy Entries</button>
				</div>
			</div>
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
			computedEntries: null,
			loaded: false,
			submitting: false,
			copyId: -1,
		};
	},
	computed: {
		...mapState([
			'entries',
			'categories'
		]),
	},
	methods: {
		...mapActions([
			'getEntries',
			'copyEntries',
			'getCategories'
		]),
		async submitCopyEntries(){
			this.submitting = true;
			try {
				await this.copyEntries({
					id: this.category.id,
					copyid: this.copyId,
				});
			} finally {
				this.submitting = false;
			}
		}
	},
	async mounted () {
		Promise.all([
			this.entries ? Promise.resolve() : this.getEntries(),
			this.categories ? Promise.resolve() : this.getCategories(),
		]).then(() => {
			this.computedEntries = this.entries.filter(entry => entry.categoryId === this.category.id);
			this.loaded = true;
		});
	},
};
</script>
<style lang="scss">
.show-picker-overflow-wrap{
	overflow: hidden;
}
.show-picker-entries{
	display: flex;
	flex-wrap: wrap;
	overflow-y: auto;
	overflow-x: hidden;
	height: 70vh;

	.show-picker-entry {
		flex: 0 0 calc(100%/4);

		.box {
			flex-direction: row;
		}
	}
}
.show-cover {
    max-width: 100px;
    // margin-right: 0.4rem;
    border-radius: 3px;
    overflow: hidden;
}
</style>