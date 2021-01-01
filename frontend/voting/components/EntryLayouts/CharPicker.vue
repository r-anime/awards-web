<template>
	<div class="show-picker">
		<div class="show-picker-overflow-wrap">
			<div class="show-picker-search-bar">
				<div class="field has-addons">
					<p class="control has-icons-left is-expanded">
						<input
							class="input is-primary is-medium"
							type="text"
							:value="search"
							@input="handleInput($event)"
							placeholder="Search by title..."
						/>
						<span class="icon is-medium is-left">
							<i class="fas fa-search"/>
						</span>
					</p>
					<div class="control">
						<span
							class="button is-medium non-interactive is-primary"
							:class="{'is-loading': !loaded}"
						>
							{{total}} character{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && chars.length" class="show-picker-entries" @scroll="handleScroll($event)">
				<char-picker-entry
					v-for="char in chars"
					:key="char.id"
					:char="char"
					:selected="characterSelected(char)"
					:loading="isLoading || (!characterSelected(char) && maxNoms)"
					@action="toggleCharacter(char, $event)"
				/>
			</div>
			<div v-else-if="!search.length" class="show-picker-text">
				Please enter a show name or a character name.
			</div>
			<div v-else-if="search.length && search.length < 3" class="show-picker-text">
				Please enter a longer search query.
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
/* eslint-disable no-alert */

import Vue from 'vue';
import {mapState} from 'vuex';
import CharPickerEntry from './CharPickerEntry';
const util = require('../../../util');

const charPaginatedQuery = `query ($id: [Int], $page: Int, $perPage: Int) {
  Page(page: $page, perPage: $perPage) {
    pageInfo {
      lastPage
    }
    results: characters(id_in: $id) {
      id
      name {
        full
        alternative
      }
      image {
        large
      }
      media(sort: [START_DATE], type: ANIME, page: 1, perPage: 5) {
        nodes {
          id
          title {
            romaji
            english
		  }
		  startDate {
			year
			month
			day
		  }
        }
      }
      siteUrl
    }
  }
}
`;

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
			loading: [],
		};
	},
	computed: {
		...mapState([
			'votingCats',
		]),
		maxNoms () {
			return this.value[this.category.id].length >= 10;
		},
		isLoading () {
			return this.loading.includes(true);
		},
	},
	methods: {
		handleScroll (event) {
			// console.log(event.target.scrollTop);
			this.$emit('scroll-picker', event.target.scrollTop);
		},
		handleInput (event) {
			// TODO - this could just be a watcher
			this.search = event.target.value;
			this.loaded = false;
			clearTimeout(this.typingTimeout);
			this.typingTimeout = setTimeout(() => {
				this.sendQuery();
			}, 750);
		},
		async sendQuery () {
			if (!this.search || this.search.length < 3) {
				this.chars = [];
				this.total = 'No';
				this.loaded = true;
				return;
			}
			const returnedEntries = await fetch('/api/votes/character-search', {
				method: 'POST',
				body: JSON.stringify({
					categoryId: this.category.id,
					search: this.search,
				}),
			});
			if (returnedEntries.ok) {
				let searchResponse = await returnedEntries.json();
				searchResponse = searchResponse.map(item => item.item.character_id).slice(0, 30);
				const promiseArray = [];
				let charData = [];
				let page = 1;
				const someData = await util.paginatedQuery(charPaginatedQuery, searchResponse, page);
				charData = [...charData, ...someData.data.Page.results];
				const lastPage = someData.data.Page.pageInfo.lastPage;
				page = 2;
				while (page <= lastPage) {
					// eslint-disable-next-line no-loop-func
					promiseArray.push(new Promise(async (resolve, reject) => {
						try {
							const returnData = await util.paginatedQuery(charPaginatedQuery, searchResponse, page);
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
					this.chars = charData;
					this.total = this.chars.length;
					this.loaded = true;
				});
			} else {
				alert('No bueno');
			}
		},
		characterSelected (char) {
			return this.value[this.category.id].some(s => s.id === char.id);
		},
		async toggleCharacter (char, select = true) {
			Vue.set(this.loading, char.id, true);
			if (select) {
				if (this.characterSelected(char)) {
					Vue.set(this.loading, char.id, false);
					return;
				}
				// Limit number of nominations
				if (this.maxNoms) {
					alert('You cannot vote for any more entries.');
					Vue.set(this.loading, char.id, false);
					return;
				}

				// Check if the character is selected in another character
				// category (other than antag)
				if (!this.category.name.includes('Antagonist')) {
					// Get all the other character categories, other than antagonist
					const charCats = this.votingCats.filter(cat => cat.entryType === 'characters' && cat.id !== this.category.id);
					for (const cat of charCats) {
						// Search for this character in the other categories
						const charIndex = this.value[cat.id].findIndex(character => character.id === char.id);
						if (charIndex !== -1) { // If we find it...
							// Confirm that the user wants to move their vote
							if (confirm(`This character is already selected in the ${cat.name} category. Do you want to remove your vote for them in that category?`)) {
								// If they want to move it, we need to update the entry in the other category
								// eslint-disable-next-line no-await-in-loop
								const response = await fetch('/api/votes/delete', {
									method: 'POST',
									body: JSON.stringify({
										category_id: cat.id,
										entry_id: char.id,
										anilist_id: null,
										theme_name: null,
									}),
								});
								if (!response.ok) {
									// eslint-disable-next-line no-alert
									alert('Something went wrong deleting your vote');
									Vue.set(this.loading, char.id, false);
									return;
								}
								this.value[cat.id].splice(charIndex, 1);
								this.$emit('input', this.value);
							} else {
								Vue.set(this.loading, char.id, false);
								return;
							}
						}
					}
				}
				const response = await fetch('/api/votes/submit', {
					method: 'POST',
					body: JSON.stringify({
						category_id: this.category.id,
						entry_id: char.id,
						anilist_id: null,
						theme_name: null,
					}),
				});
				if (!response.ok) {
					// eslint-disable-next-line no-alert
					alert('Something went wrong submitting your selection');
					Vue.set(this.loading, char.id, false);
					return;
				}
				this.value[this.category.id].push(char);
				this.$emit('input', this.value);
				Vue.set(this.loading, char.id, false);
			} else {
				if (!this.characterSelected(char)) return;
				const index = this.value[this.category.id].findIndex(c => c.id === char.id);
				const arr = [...this.value[this.category.id]];
				arr.splice(index, 1);
				const response = await fetch('/api/votes/delete', {
					method: 'POST',
					body: JSON.stringify({
						category_id: this.category.id,
						entry_id: char.id,
						anilist_id: null,
						theme_name: null,
					}),
				});
				if (!response.ok) {
					// eslint-disable-next-line no-alert
					alert('Something went wrong submitting your selection');
					Vue.set(this.loading, char.id, false);
					return;
				}
				this.value[this.category.id] = arr;
				this.$emit('input', this.value);
				Vue.set(this.loading, char.id, false);
			}
		},
	},
	watch: {
		category () {
			this.search = '';
			this.chars = [];
			this.total = 'No';
			this.loaded = true;
		},
	},
	mounted () {
		this.search = '';
		this.chars = [];
		this.total = 'No';
		this.loaded = true;
	},
};
</script>
