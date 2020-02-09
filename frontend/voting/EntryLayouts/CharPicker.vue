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
		<div v-else-if="value[category.id].length" class="char-picker-overflow-wrap">
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
		<div v-else class="char-picker-text">
			You don't have any selections in this category yet. Get started on the search tab.
		</div>
		<a href="https://forms.gle/GzkoRQmuF6G8bLE78" style="display: block; text-align: center; margin-bottom: 2px;">Are we missing something?</a>
	</div>
</template>

<script>
import {mapState} from 'vuex';
import CharPickerEntry from './CharPickerEntry';
const queries = require('../../anilistQueries');

export default {
	components: {
		CharPickerEntry,
	},
	props: {
		category: Object,
		value: Object,
	},
	data () {
		return {
			loaded: true,
			typingTimeout: null,
			search: '',
			chars: [],
			total: 'No',
			selectedTab: 'selections',
		};
	},
	computed: {
		...mapState([
			'votingCats',
		]),
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
		findDate (node) {
			let date;
			try {
				date = new Date(node.endDate.year, node.endDate.month, node.endDate.day);
			} catch (err) {
				date = new Date(2016, 0, 0);
			}
			return date;
		},
		async sendQuery () {
			if (!this.search) {
				this.loaded = true;
				this.chars = [];
				this.total = 'No';
				return;
			}
			const response = await fetch('https://graphql.anilist.co', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query: queries.charQuery,
					variables: {
						search: this.search,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.chars = data.data.character.results;
			const promise = new Promise((resolve, reject) => {
				try {
					for (const char of this.chars) {
						if (this.category.name.includes('Main')) char.media.edges = char.media.edges.filter(edge => edge.characterRole === 'MAIN');
						else if (this.category.name.includes('Supporting')) char.media.edges = char.media.edges.filter(edge => edge.characterRole === 'SUPPORTING');
						char.media.nodes = char.media.nodes.filter(node => queries.eligibilityStart <= this.findDate(node) && this.findDate(node) <= queries.eligibilityEnd);
					}
					this.chars = this.chars.filter(char => char.media.nodes.length !== 0 && char.media.edges.length !== 0);
					// this loop really needs to run after the first one to avoid errors
					for (const [count, char] of this.chars.entries()) {
						if (!char.media.nodes.some(media => !queries.blacklist.includes(media.id))) this.chars.splice(count, 1);
					}
					resolve();
				} catch (err) {
					reject(err);
				}
			});
			promise.then(() => {
				this.total = this.chars.length;
				this.loaded = true;
			});
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
		category () {
			this.search = '';
			this.selectedTab = 'selections';
			this.chars = [];
		},
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
