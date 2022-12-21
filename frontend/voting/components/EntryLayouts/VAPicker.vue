<template>
	<div class="show-picker">
		<div class="show-picker-overflow-wrap">
			<div class="show-picker-search-bar">
				<div class="field has-addons">
					<p class="control has-icons-left is-expanded">
						<input
							class="input is-primary is-medium"
							type="text"
							v-model="search"
							placeholder="Search by title..."
							:disabled="lockSearch"
						/>
						<span class="icon is-medium is-left has-text-platinum">
							<i class="fas fa-search"/>
						</span>
					</p>
				</div>
			</div>

			<div v-if="loaded && filteredShows.length" class="show-picker-entries" @scroll="handleScroll($event)">
				<VAPickerEntry
					v-for="show in filteredShows"
					:key="show.id"
					:va="show"
					:selected="showSelected(show)"
					:loading="isLoading || (!showSelected(show) && maxNoms)"
					@action="toggleShow(show, $event)"
				/>
			</div>
			<div v-else-if="loaded" class="show-picker-text">
				{{search ? 'No results :(' : ''}}
			</div>
			<div v-else class="show-picker-text">
				Loading...
			</div>
		</div>
	</div>
</template>

<script>
/* eslint-disable vue/no-mutating-props */
import Vue from 'vue';
import {mapActions, mapState} from 'vuex';
import VAPickerEntry from './VAPickerEntry';

export default {
	components: {
		VAPickerEntry,
	},
	props: {
		value: Object,
		category: Object,
		entries: Array,
	},
	data () {
		return {
			loaded: false,
			typingTimeout: null,
			search: '',
			total: 'No',
			showData: null,
			loading: [],
			lockSearch: false,
		};
	},
	computed: {
		...mapState([
			'items',
			'loadingprogress',
		]),
		showIDs () {
			return this.entries.map(show => show.anilist_id);
		},
		maxNoms () {
			return this.value[this.category.id].length >= 10;
		},
		isLoading () {
			return this.loading.includes(true);
		},
		categoryItems () {
			const _this = this;
			let items = [];

			if (this.entries && this.entries.length > 0){
				items = this.items.filter(item => {
					return _this.entries.some(e => ((e.search == 'internal' && e.anilist_id == item.id) || (e.search != 'internal' && e.anilist_id == item.anilistID)) && item.type == 'va');
				})
			} else {
				items = this.items.filter (item => item.type == 'va');
			}

			return items.filter(i => {
				let r = true;
				if (i.parent){
					if (i.parent.parent){
						r = r && i.parent.parent.type == 'anime';
						r = r && !(String(i.parent.parent.mediatype) == 'MOVIE' || String(i.parent.parent.mediatype) == 'ONA' || String(i.parent.parent.mediatype) == 'MUSIC');
					}
					r = r && i.parent.type == 'char';
				}
				return r;
			});
		},
		filteredShows () {
			let items = [];
			if (this.search == ""){
				items = this.categoryItems;
			}
			else {
				const _filter = this.search.toLowerCase();
				items = this.categoryItems.filter((item) => {
					let filter = (String(item.english).toLowerCase().includes(_filter) || String(item.romanji).toLowerCase().includes(_filter)  || String(item.names).toLowerCase().includes(_filter));
					if (item.parent) {
						filter = filter || (String(item.parent.english).toLowerCase().includes(_filter) || String(item.parent.romanji).toLowerCase().includes(_filter)  || String(item.parent.names).toLowerCase().includes(_filter));
						if (item.parent.parent) {
							filter = filter || (String(item.parent.parent.english).toLowerCase().includes(_filter) || String(item.parent.parent.romanji).toLowerCase().includes(_filter)  || String(item.parent.parent.names).toLowerCase().includes(_filter));
						}
					}
					return filter;
				});
			}
			return items;
			// return items.slice(0, 200);
		}
	},
	methods: {
		...mapActions([
			'getItems'
		]),
		handleScroll (event) {
			// console.log(event.target.scrollTop);
			this.$emit('scroll-picker', event.target.scrollTop);
		},
		showSelected (show) {
			console.log(show);
			return this.value[this.category.id].some(s => (s.id === show.id));
		},
		async toggleShow (show, select = true) {
			Vue.set(this.loading, show.id, true);
			if (select) {
				if (this.showSelected(show)) {
					Vue.set(this.loading, show.id, false);
					return;
				}
				// Limit number of nominations
				if (this.maxNoms) {
					// eslint-disable-next-line no-alert
					alert('You cannot vote for any more entries.');
					Vue.set(this.loading, show.id, false);
					return;
				}
				const response = await fetch('/api/votes/submit', {
					method: 'POST',
					body: JSON.stringify({
						category_id: this.category.id,
						entry_id: show.id,
						anilist_id: null,
						theme_name: null,
					}),
				});
				if (!response.ok) {
					// eslint-disable-next-line no-alert
					alert('Something went wrong submitting your selection');
					Vue.set(this.loading, show.id, false);
					return;
				}
				this.value[this.category.id].push(show);
				this.$emit('input', this.value);
				Vue.set(this.loading, show.id, false);
			} else {
				if (!this.showSelected(show)) return;
				const index = this.value[this.category.id].findIndex(s => s.id === show.id);
				const arr = [...this.value[this.category.id]];
				arr.splice(index, 1);
				const response = await fetch('/api/votes/delete', {
					method: 'POST',
					body: JSON.stringify({
						category_id: this.category.id,
						entry_id: show.id,
						anilist_id: null,
						theme_name: null,
					}),
				});
				if (!response.ok) {
					// eslint-disable-next-line no-alert
					alert('Something went wrong submitting your selection');
					return;
				}
				this.value[this.category.id] = arr;
				this.$emit('input', this.value);
				Vue.set(this.loading, show.id, false);
			}
		},
	},
	watch: {
		async category () {
			this.loaded = false;
			this.search = '';
			this.loaded = true;
		},
	},
	async mounted () {
		if (!this.items || this.items.length == 0){
			await this.getItems();
		}
		this.loaded = true;
	},
};
</script>
