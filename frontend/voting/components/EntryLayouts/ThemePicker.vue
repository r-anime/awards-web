<!--This one pulls music from the DB and stuff. Should be straightforward.-->
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
							:value="search"
							@input="handleInput($event)"
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
							{{total}} theme{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && shows.length" class="show-picker-entries">
				<ThemePickerEntry
					v-for="show in shows"
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
		<div v-else-if="value[category.id].length && loaded" class="show-picker-overflow-wrap">
			<div class="show-picker-entries">
				<ThemePickerEntry
					v-for="show in value[category.id]"
					:key="'selected' + show.id"
					:show="show"
					:selected="showSelected(show)"
					@action="toggleShow(show, $event)"
				/>
			</div>
		</div>
		<div v-else-if="!loaded" class="char-picker-text">
			Loading...
		</div>
		<div v-else class="show-picker-text">
			You don't have any selections in this category yet. Get started on the search tab.
		</div>
	</div>
</template>

<script>
import ThemePickerEntry from './ThemePickerEntry';
import {mapActions, mapState} from 'vuex';
const Fuse = require('fuse.js');
const util = require('../../../util');
const aq = require('../../../anilistQueries');

const options = {
	shouldSort: true,
	threshold: 0.3,
	location: 0,
	distance: 70,
	maxPatternLength: 64,
	minMatchCharLength: 3,
	keys: [
		'title',
		'anime',
	],
};

export default {
	components: {
		ThemePickerEntry,
	},
	props: {
		value: Object,
		category: Object,
		entries: Array,
	},
	computed: {
		...mapState([
			'themes',
			'selections',
		]),
		showIDs () {
			return this.entries.map(show => show.anilist_id);
		},
	},
	data () {
		return {
			loaded: false,
			typingTimeout: null,
			search: '',
			shows: [],
			showData: [],
			themeData: [],
			backup: [],
			total: 'No',
			selectedTab: 'selections',
		};
	},
	methods: {
		...mapActions(['getThemes']),
		handleInput (event) {
			// TODO - this could just be a watcher
			this.search = event.target.value;
			this.loaded = false;
			clearTimeout(this.typingTimeout);
			this.typingTimeout = setTimeout(() => {
				this.searchThemes();
			}, 750);
		},
		searchThemes () {
			if (!this.search || this.search.length <= 2) {
				this.shows = this.backup;
				this.total = this.shows.length;
				this.loaded = true;
				return;
			}
			const entries = this.backup;
			const fuse = new Fuse(entries, options);
			this.shows = fuse.search(this.search);
			this.total = this.shows.length;
			this.loaded = true;
		},
		showSelected (show) {
			return this.value[this.category.id].some(s => s.id === show.id);
		},
		toggleShow (show, select = true) {
			if (select) {
				if (this.showSelected(show)) return;
				// Limit number of nominations
				if (this.value[this.category.id].length >= 50) {
					alert("You cannot vote for any more entries.");
					this.selectedTab = 'selections';
					return;
				}
				const themeIndex = this.value[this.category.id].findIndex(theme => theme.title === show.title);
				if (themeIndex !== -1) { // If we find it...
					// Confirm that the user wants to move their vote
					if (confirm(`You have already selected another version of this theme. Do you want to move your vote to this version.`)) {
						// If they want to move it, we need to update the entry in the other category
						this.value[this.category.id].splice(themeIndex, 1);
					} else {
						// If they cancel out, return and change tab to avoid visual glitch
						this.selectedTab = 'selections';
						return;
					}
				}

				this.value[this.category.id].push(show);
				this.$emit('input', this.value);
			} else {
				if (!this.showSelected(show)) return;
				const index = this.value[this.category.id].findIndex(s => s.id === show.id);
				const arr = [...this.value[this.category.id]];
				arr.splice(index, 1);
				this.value[this.category.id] = arr;
				this.$emit('input', this.value);
			}
		},
	},
	async mounted () {
		if (!this.themes) {
			this.getThemes();
		}
		const promiseArray = [];
		let showData = [];
		if (this.showIDs) {
			let page = 1;
			const someData = await util.paginatedQuery(aq.showQuerySimple, this.showIDs, page);
			showData = [...showData, ...someData.data.Page.results];
			const lastPage = someData.data.Page.pageInfo.lastPage;
			page = 2;
			while (page <= lastPage) {
				// eslint-disable-next-line no-loop-func
				promiseArray.push(new Promise(async (resolve, reject) => {
					try {
						const returnData = await util.paginatedQuery(aq.showQuerySimple, this.showIDs, page);
						resolve(returnData.data.Page.results);
					} catch (error) {
						reject(error);
					}
				}));
				page++;
			}
			Promise.all(promiseArray).then(finalData => {
				for (const data of finalData) {
					showData = [...showData, ...data];
				}
				this.entries.forEach(element => {
					const requiredTheme = this.themes.find(theme => theme.id === element.themeId);
					const requiredShow = showData.find(show => show.id === element.anilist_id);
					this.backup.push({...requiredShow, ...requiredTheme});
				});
				this.shows = this.backup;
				this.loaded = true;
			});
		} else {
			this.loaded = true;
		}
	},
	watch: {
		async category () {
			this.search = '';
			this.selectedTab = 'selections';
			this.shows = [];
			this.showData = [];
			this.themeData = [];
			const promiseArray = [];
			let showData = [];
			if (this.showIDs) {
				let page = 1;
				const someData = await util.paginatedQuery(aq.showQuerySimple, this.showIDs, page);
				showData = [...showData, ...someData.data.Page.results];
				const lastPage = someData.data.Page.pageInfo.lastPage;
				page = 2;
				while (page <= lastPage) {
					// eslint-disable-next-line no-loop-func
					promiseArray.push(new Promise(async (resolve, reject) => {
						try {
							const returnData = await util.paginatedQuery(aq.showQuerySimple, this.showIDs, page);
							resolve(returnData.data.Page.results);
						} catch (error) {
							reject(error);
						}
					}));
					page++;
				}
				Promise.all(promiseArray).then(finalData => {
					for (const data of finalData) {
						showData = [...showData, ...data];
					}
					this.entries.forEach(element => {
						const requiredTheme = this.themes.find(theme => theme.id === element.themeId);
						const requiredShow = showData.find(show => show.id === element.anilist_id);
						this.backup.push({...requiredShow, ...requiredTheme});
					});
					this.shows = this.backup;
					this.loaded = true;
				});
			} else {
				this.loaded = true;
			}
		},
	},
};
</script>

<style lang="scss">
.tabs.show-picker-tabs {
	margin-bottom: 0 !important;
}
.show-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 410px);
	overflow-y: auto;
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
</style>
