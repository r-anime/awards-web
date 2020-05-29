<!--God there's VAs too. Pull from anilist-->
<template>
	<div class="va-picker">
		<div class="tabs is-centered va-picker-tabs">
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

		<div v-if="selectedTab === 'search'" class="va-picker-overflow-wrap">
			<div class="va-picker-search-bar">
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
							{{total}} role{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && vas.length" class="va-picker-entries">
				<VAPickerEntry
					v-for="va in vas"
					:key="va.id"
					:va="va"
					:selected="showSelected(va)"
					@action="toggleShow(va, $event)"
				/>
			</div>
			<div v-else-if="loaded" class="va-picker-text">
				{{search ? 'No results :(' : ''}}
			</div>
			<div v-else class="va-picker-text">
				Loading...
			</div>
		</div>
		<div v-else-if="value[category.id].length" class="va-picker-overflow-wrap">
			<div class="va-picker-entries">
				<VAPickerEntry
					v-for="va in value[category.id]"
					:key="'selected' + va.id"
					:va="va"
					:selected="showSelected(va)"
					@action="toggleShow(va, $event)"
				/>
			</div>
		</div>
		<div v-else class="va-picker-text">
			You don't have any selections in this category yet. Get started on the search tab.
		</div>
		<a href="https://forms.gle/GzkoRQmuF6G8bLE78" style="display: block; text-align: center; margin-bottom: 2px;">Are we missing something?</a>
	</div>
</template>

<script>
import VAPickerEntry from './VAPickerEntry';
const queries = require('../../../anilistQueries');
const util = require('../../../util');
const Fuse = require('fuse.js');

const options = {
	shouldSort: true,
	threshold: 0.3,
	location: 0,
	distance: 70,
	maxPatternLength: 64,
	minMatchCharLength: 3,
	keys: [
		'name.full',
		'name.alternative',
		'name.native',
		'media.edges.voiceActors.name.full',
		'media.edges.voiceActors.name.alternative',
		'media.edges.voiceActors.name.native',
		'media.nodes.title.romaji',
		'media.nodes.title.english',
		'media.nodes.title.native',
		'media.nodes.title.userPreferred',
	],
};

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
			vas: [],
			total: 'No',
			selectedTab: 'selections',
			vaData: null,
		};
	},
	computed: {
		vaIDs () {
			return this.entries.map(char => char.character_id);
		},
	},
	methods: {
		handleInput (event) {
			// TODO - this could just be a watcher
			this.search = event.target.value;
			this.loaded = false;
			clearTimeout(this.typingTimeout);
			this.typingTimeout = setTimeout(() => {
				this.sendQuery();
			}, 750);
		},
		sendQuery () {
			if (!this.search || this.search.length <= 2) {
				this.vas = this.vaData;
				this.total = this.vas.length;
				this.loaded = true;
				return;
			}
			const entries = this.vaData;
			const fuse = new Fuse(entries, options);
			this.vas = fuse.search(this.search);
			this.total = this.vas.length;
			this.loaded = true;
		},
		showSelected (va) {
			return this.value[this.category.id].some(s => s.id === va.id);
		},
		toggleShow (va, select = true) {
			if (select) {
				if (this.showSelected(va)) return;
				// Limit number of nominations
				if (this.value[this.category.id].length >= 50) {
					alert('You cannot vote for any more entries.');
					this.selectedTab = 'selections';
					return;
				}
				this.value[this.category.id].push(va);
				this.$emit('input', this.value);
			} else {
				if (!this.showSelected(va)) return;
				const index = this.value[this.category.id].findIndex(v => v.id === va.id);
				const arr = [...this.value[this.category.id]];
				arr.splice(index, 1);
				this.value[this.category.id] = arr;
				this.$emit('input', this.value);
			}
		},
	},
	watch: {
		async category () {
			this.loaded = false;
			this.search = '';
			this.selectedTab = 'selections';
			const promiseArray = [];
			let charData = [];
			if (this.vaIDs) {
				let page = 1;
				const someData = await util.paginatedQuery(queries.charQuerySimple, this.vaIDs, page);
				charData = [...charData, ...someData.data.Page.results];
				const lastPage = someData.data.Page.pageInfo.lastPage;
				page = 2;
				while (page <= lastPage) {
					// eslint-disable-next-line no-loop-func
					promiseArray.push(new Promise(async (resolve, reject) => {
						try {
							const returnData = await util.paginatedQuery(queries.charQuerySimple, this.vaIDs, page);
							resolve(returnData.data.Page.results);
						} catch (error) {
							reject(error);
						}
					}));
					page++;
				}
				Promise.all(promiseArray).then(finalData => {
					for (const data of finalData) {
						charData = [...charData, ...data];
					}
					this.vaData = charData;
					this.vas = charData;
					this.vas = this.vas.filter(va => va.media.nodes.length > 0 && va.media.edges.length > 0);
					this.vas = this.vas.filter(va => va.media.edges[0].voiceActors.length > 0);
					this.loaded = true;
				});
			}
			this.loaded = true;
		},
	},
	async mounted () {
		const promiseArray = [];
		let charData = [];
		if (this.vaIDs) {
			let page = 1;
			const someData = await util.paginatedQuery(queries.charQuerySimple, this.vaIDs, page);
			charData = [...charData, ...someData.data.Page.results];
			const lastPage = someData.data.Page.pageInfo.lastPage;
			page = 2;
			while (page <= lastPage) {
				// eslint-disable-next-line no-loop-func
				promiseArray.push(new Promise(async (resolve, reject) => {
					try {
						const returnData = await util.paginatedQuery(queries.charQuerySimple, this.vaIDs, page);
						resolve(returnData.data.Page.results);
					} catch (error) {
						reject(error);
					}
				}));
				page++;
			}
			Promise.all(promiseArray).then(finalData => {
				for (const data of finalData) {
					charData = [...charData, ...data];
				}
				this.vaData = charData;
				this.vas = charData;
				this.vas = this.vas.filter(va => va.media.nodes.length > 0 && va.media.edges.length > 0);
				this.vas = this.vas.filter(va => va.media.edges[0].voiceActors.length > 0);
				this.loaded = true;
			});
		}
		this.loaded = true;
	},
};
</script>

<style lang="scss">
.tabs.va-picker-tabs {
	margin-bottom: 0 !important;
}
.va-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 410px);
	overflow-y: auto;
}
.va-picker-search-bar {
	margin: 0 auto;
	max-width: 500px;
	padding: 0.75rem 0.75rem 0;
}
.va-picker-entries {
	display: flex;
	flex-wrap: wrap;
	padding: 0.375rem;
}
.va-picker-entry {
	flex: 0 0 calc(100% / 3);
	padding: 0.375rem;

	> div {
		height: 100%;
	}
}
.va-picker-text {
	flex: 0 1 100%;
	padding: 0.75rem;
	text-align: center;
}
</style>
