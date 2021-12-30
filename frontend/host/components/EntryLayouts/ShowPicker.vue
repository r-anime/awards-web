<template>
	<div class="show-picker">
		<div class="tabs is-centered show-picker-tabs">
			<ul>
				<li :class="{'is-active': selectedTab === 'selections'}">
					<a @click="selectedTab = 'selections'">
						Selections
					</a>
				</li>
				<li :class="{'is-active': selectedTab === 'search'}">
					<a @click="selectedTab = 'search'">
						Search
					</a>
				</li>
			</ul>
		</div>

		<div v-if="selectedTab === 'search'" class="show-picker-overflow-wrap">
			<div class="show-picker-search-bar">
				<div class="field has-addons">
					<p class="control has-icons-left is-expanded">
						<input
							class="input is-small"
							type="text"
							v-model="search"
							placeholder="Search by title..."
						/>
						<span class="icon is-small is-left">
							<i class="fas fa-search"/>
						</span>
					</p>
					<div class="control">
						<span
							class="button is-small is-static"
							:class="{'is-loading': !loaded}"
						>
							{{filteredItems.length}} show{{filteredItems.length === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && filteredItems.length" class="show-picker-entries">
				<show-picker-entry
					v-for="show in filteredItems"
					:key="show.id"
					:show="show"
					:selected="showSelected(show)"
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
		<div v-else-if="selections.length && loaded" class="show-picker-overflow-wrap">
			<div class="show-picker-entries">
				<show-picker-entry
					v-for="show in selections"
					:key="'selected' + show.id"
					:show="show"
					:selected="showSelected(show)"
					@action="toggleShow(show, $event)"
				/>
			</div>
		</div>
		<div v-else-if="!loaded" class="show-picker-text">
			Loading...
		</div>
		<div v-else class="show-picker-text">
			You don't have any selections in this category yet. Get started on the search tab.
		</div>
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
				@click="clear"
			>
				Clear Selections
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
import ShowPickerEntry from './ShowPickerEntry';

export default {
	components: {
		ShowPickerEntry,
	},
	props: {
		value: Array,
		category: Object,
	},
	data () {
		return {
			loaded: false,
			typingTimeout: null,
			search: '',
			selections: [],
			selectedTab: 'selections',
			submitting: false,
		};
	},
	computed: {
		...mapState([
			'items',
		]),
		filteredItems(){
			let items = [];
			if (this.search == ""){
				items = this.items.filter((item) => {
					return item.type == 'anime';
				});
			} else {
				const _filter = this.search.toLowerCase();
				items = this.items.filter((item) => {
					return (String(item.english).toLowerCase().includes(_filter) || String(item.romanji).toLowerCase().includes(_filter)) && item.type == 'anime';
				});
			}
			return items.slice(0, 50);
		},
		showIds () {
			return this.value.map(show =>
				({
					id: show.anilist_id,
					search: show.search,
				})
			);
		},
		submissions () {
			return this.selections.map(item => {
				if (item.anilistID == -1){
					return {
						anilist_id: item.id,
						character_id: null,
						themeId: null,
						categoryId: this.category.id,
						search: 'internal',
					}
				} else {
					return {
						anilist_id: item.anilistID,
						character_id: null,
						themeId: null,
						categoryId: this.category.id,
						search: 'anilist',
					}
				}
			});
		},
	},
	methods: {
		...mapActions([
			'updateEntries',
			'getItems'
		]),
		handleInput (event) {
			// TODO - this could just be a watcher
			this.search = event.target.value;
			this.loaded = false;
			clearTimeout(this.typingTimeout);
			this.typingTimeout = setTimeout(() => {
				this.sendQuery();
			}, 750);
		},
		showSelected (show) {
			if (show.id > 0){
				return this.selections.some(s => s.id === show.id);
			} else {
				return this.selections.some(s => s.anilistID === show.anilistID);
			}
		},
		toggleShow (show, select = true) {
			if (select) {
				if (this.showSelected(show)) return;
				this.selections.push(show);
			} else {
				if (!this.showSelected(show)) return;
				const index = this.selections.findIndex(s => s.id === show.id);
				this.selections.splice(index, 1);
			}
		},
		async submit () {
			this.submitting = true;
			try {
				await this.updateEntries({
					id: this.category.id,
					entries: this.submissions,
				});
			} finally {
				this.submitting = false;
			}
		},
		selectAll () {
			for (const show of this.filteredItems) {
				this.toggleShow(show, true);
			}
		},
		unselectAll () {
			for (const show of this.filteredItems) {
				this.toggleShow(show, false);
			}
		},
		clear () {
			for (const show of this.selections) {
				this.toggleShow(show, false);
			}
		},
	},
	async mounted () {
		await this.getItems();
		for (const show of this.showIds){
			let item;
			if (show.search == 'internal'){
				item = this.items.find(i => i.id == show.id);
			} else {
				item = this.items.find(i => i.anilistID == show.id);
			}
			if (item){
				this.toggleShow(item, true);
			} else {
				this.toggleShow({
					anilistID: show.id,
					english: String(show.id),
					romanji: String(show.id),
					type: 'anime',
					id: -1,
					image: 'none'
				}, true);
			}
		}
		this.loaded = true;
	},
};
</script>

<style lang="scss">
.tabs.show-picker-tabs {
	margin-bottom: 0 !important;
}
.show-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 141px - 46px);
	overflow-y: auto;
}
.show-picker-search-bar {
	margin: 0 auto;
	max-width: 500px;
	padding: 0.75rem 0.75rem 0;
}
.show-picker-entries {
	display: flex;
	flex-wrap: wrap;
	padding: 0.375rem;
}
.show-picker-entry {
	flex: 0 0 calc(100% / 3);
	padding: 0.375rem;

	> div {
		height: 100%;
	}
}
.show-picker-text {
	flex: 0 1 100%;
	padding: 0.75rem;
	text-align: center;
}

.show-picker-overflow-wrap::-webkit-scrollbar {
    width: 8px;
}

.show-picker-overflow-wrap::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.7);
    border-radius: 20px;
}

.show-picker-overflow-wrap::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 20px;
}

.submit-wrapper {
	box-shadow: inset 0 1px #dbdbdb;
	text-align: center;
	padding: 5px;
}
</style>
