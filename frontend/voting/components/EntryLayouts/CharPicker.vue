<template>
	<div class="char-picker">
		<div class="tabs is-centered char-picker-tabs">
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

		<div v-if="selectedTab === 'search'" class="char-picker-overflow-wrap">
			<br>
			<div class="is-size-7 is-centered has-text-centered">
				Try searching a show/character by its full name on AniList if partial searches don't work.
			</div>
			<div class="char-picker-search-bar">
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
							{{total}} character{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && chars.length" class="char-picker-entries">
				<char-picker-entry
					v-for="char in chars"
					:key="char.id"
					:char="char"
					:selected="characterSelected(char)"
					@action="toggleCharacter(char, $event)"
				/>
			</div>
			<div v-else-if="loaded" class="char-picker-text">
				{{search ? 'No results :(' : ''}}
			</div>
			<div v-else class="char-picker-text">
				Loading...
			</div>
		</div>
		<div v-else-if="value[category.id].length && loaded" class="char-picker-overflow-wrap">
			<div class="char-picker-entries">
				<char-picker-entry
					v-for="char in value[category.id]"
					:key="'selected' + char.id"
					:char="char"
					:selected="characterSelected(char)"
					@action="toggleCharacter(char, $event)"
				/>
			</div>
		</div>
		<div v-else-if="!loaded" class="char-picker-text">
			Loading...
		</div>
		<div v-else class="char-picker-text">
			You don't have any selections in this category yet. Get started on the search tab.
		</div>
	</div>
</template>

<script>
import {mapState} from 'vuex';
import CharPickerEntry from './CharPickerEntry';
const queries = require('../../../anilistQueries');
const util = require('../../../util');
import Fuse from 'fuse.js/dist/fuse.basic.min';

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
		'media.nodes.title.romaji',
		'media.nodes.title.english',
		'media.nodes.title.native',
		'media.nodes.title.userPreferred',
	],
};

export default {
	components: {
		CharPickerEntry,
	},
	props: {
		category: Object,
		value: Object,
		entries: Array,
	},
	data () {
		return {
			loaded: false,
			typingTimeout: null,
			search: '',
			chars: [],
			total: 'No',
			selectedTab: 'selections',
			charData: null,
		};
	},
	computed: {
		...mapState([
			'votingCats',
		]),
		charIDs () {
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
				this.chars = this.charData;
				this.total = 0;
				this.loaded = true;
				return;
			}
			const entries = this.charData;
			const fuse = new Fuse(entries, options);
			this.chars = fuse.search(this.search);
			this.chars = this.chars.map(char => char.item);
			this.total = this.chars.length;
			this.loaded = true;
		},
		characterSelected (char) {
			return this.value[this.category.id].some(s => s.id === char.id);
		},
		toggleCharacter (char, select = true) {
			if (select) {
				if (this.characterSelected(char)) return;
				// Limit number of nominations
				if (this.value[this.category.id].length >= 50) {
					alert('You cannot vote for any more entries.');
					this.selectedTab = 'selections';
					return;
				}

				// Check if the character is selected in another character
				// category (other than antag)
				if (!this.category.name.includes('Antagonist')) {
					// Get all the other character categories, other than antagonist
					const charCats = this.votingCats.filter(cat => cat.entryType === 'characters' && !cat.name.includes('Antagonist') && cat.id !== this.category.id);
					for (const cat of charCats) {
						// Search for this character in the other categories
						const charIndex = this.value[cat.id].findIndex(character => character.id === char.id);
						if (charIndex !== -1) { // If we find it...
							// Confirm that the user wants to move their vote
							if (confirm(`This character is already selected in the ${cat.name} category. Do you want to remove your vote for them in that category?`)) {
								// If they want to move it, we need to update the entry in the other category
								this.value[cat.id].splice(charIndex, 1);
							} else {
								// If they cancel out, return and change tab to avoid visual glitch
								this.selectedTab = 'selections';
								return;
							}
						}
					}
				}

				this.value[this.category.id].push(char);
				this.$emit('input', this.value);
			} else {
				if (!this.characterSelected(char)) return;
				const index = this.value[this.category.id].findIndex(c => c.id === char.id);
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
			if (this.charIDs) {
				let page = 1;
				const someData = await util.paginatedQuery(queries.charQuerySimple, this.charIDs, page);
				charData = [...charData, ...someData.data.Page.results];
				const lastPage = someData.data.Page.pageInfo.lastPage;
				page = 2;
				while (page <= lastPage) {
					// eslint-disable-next-line no-loop-func
					promiseArray.push(new Promise(async (resolve, reject) => {
						try {
							const returnData = await util.paginatedQuery(queries.charQuerySimple, this.charIDs, page);
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
					this.charData = charData;
					this.chars = charData;
					this.loaded = true;
				});
			} else {
				this.loaded = true;
			}
		},
	},
	async mounted () {
		const promiseArray = [];
		let charData = [];
		if (this.charIDs.length) {
			let page = 1;
			const someData = await util.paginatedQuery(queries.charQuerySimple, this.charIDs, page);
			charData = [...charData, ...someData.data.Page.results];
			const lastPage = someData.data.Page.pageInfo.lastPage;
			page = 2;
			while (page <= lastPage) {
				// eslint-disable-next-line no-loop-func
				promiseArray.push(new Promise(async (resolve, reject) => {
					try {
						const returnData = await util.paginatedQuery(queries.charQuerySimple, this.charIDs, page);
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
				this.charData = charData;
				this.chars = charData;
				this.loaded = true;
			});
		} else {
			this.loaded = true;
		}
	},
};
</script>

<style lang="scss">
.tabs.char-picker-tabs {
	margin-bottom: 0 !important;
}
.char-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 410px);
	overflow-y: auto;
}
.char-picker-search-bar {
	margin: 0 auto;
	max-width: 500px;
	padding: 0.75rem 0.75rem 0;
}
.char-picker-entries {
	display: flex;
	flex-wrap: wrap;
	padding: 0.375rem;
}
.char-picker-entry {
	flex: 0 0 calc(100% / 3);
	padding: 0.375rem;

	> div {
		height: 100%;
	}
}
.char-picker-text {
	flex: 0 1 100%;
	padding: 0.75rem;
	text-align: center;
}
</style>
