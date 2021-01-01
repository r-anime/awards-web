<!--God there's VAs too. Pull from anilist-->
<template>
	<div class="va-picker">
		<div class="va-picker-overflow-wrap">
			<div class="va-picker-search-bar">
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
					:loading="isLoading || (!showSelected(va) && maxNoms)"
					@action="toggleShow(va, $event)"
				/>
			</div>
			<div v-else-if="!search.length" class="char-picker-text">
				Please enter a show name, a character name or a VA.
			</div>
			<div v-else-if="search.length && search.length < 3" class="char-picker-text">
				Please enter a longer search query.
			</div>
			<div v-else-if="loaded" class="va-picker-text">
				{{search ? 'No results :(' : ''}}
			</div>
			<div v-else class="va-picker-text">
				Loading...
			</div>
		</div>
	</div>
</template>

<script>
/* eslint-disable vue/no-mutating-props */
/* eslint-disable no-alert */

import Vue from 'vue';
import VAPickerEntry from './VAPickerEntry';
const util = require('../../../util');

const VAPaginatedQuery = `query ($id: [Int], $page: Int, $perPage: Int) {
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
        edges {
          node {
            id
            startDate {
              year
              month
              day
            }
            title {
              romaji
              english
            }
          }
          voiceActors(language: JAPANESE) {
            id
            name {
              full
              alternative
            }
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
			loading: [],
		};
	},
	computed: {
		maxNoms () {
			return this.value[this.category.id].length >= 10;
		},
		isLoading () {
			return this.loading.includes(true);
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
		async sendQuery () {
			if (!this.search || this.search.length < 3) {
				this.vas = [];
				this.total = 0;
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
				const someData = await util.paginatedQuery(VAPaginatedQuery, searchResponse, page);
				charData = [...charData, ...someData.data.Page.results];
				const lastPage = someData.data.Page.pageInfo.lastPage;
				page = 2;
				while (page <= lastPage) {
					// eslint-disable-next-line no-loop-func
					promiseArray.push(new Promise(async (resolve, reject) => {
						try {
							const returnData = await util.paginatedQuery(VAPaginatedQuery, searchResponse, page);
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
					this.vas = charData;
					this.total = this.vas.length;
					this.loaded = true;
				});
			} else {
				alert('No bueno');
			}
		},
		showSelected (va) {
			return this.value[this.category.id].some(s => s.id === va.id);
		},
		async toggleShow (va, select = true) {
			Vue.set(this.loading, va.id, true);
			if (select) {
				if (this.showSelected(va)) {
					Vue.set(this.loading, va.id, false);
					return;
				}
				// Limit number of nominations
				if (this.maxNoms) {
					alert('You cannot vote for any more entries.');
					Vue.set(this.loading, va.id, false);
					return;
				}
				const response = await fetch('/api/votes/submit', {
					method: 'POST',
					body: JSON.stringify({
						category_id: this.category.id,
						entry_id: va.id,
						anilist_id: null,
						theme_name: null,
					}),
				});
				if (!response.ok) {
					// eslint-disable-next-line no-alert
					alert('Something went wrong submitting your selection');
					Vue.set(this.loading, va.id, false);
					return;
				}
				this.value[this.category.id].push(va);
				this.$emit('input', this.value);
				Vue.set(this.loading, va.id, false);
			} else {
				if (!this.showSelected(va)) return;
				const index = this.value[this.category.id].findIndex(v => v.id === va.id);
				const arr = [...this.value[this.category.id]];
				arr.splice(index, 1);
				const response = await fetch('/api/votes/delete', {
					method: 'POST',
					body: JSON.stringify({
						category_id: this.category.id,
						entry_id: va.id,
						anilist_id: null,
						theme_name: null,
					}),
				});
				if (!response.ok) {
					// eslint-disable-next-line no-alert
					alert('Something went wrong submitting your selection');
					Vue.set(this.loading, va.id, false);
					return;
				}
				this.value[this.category.id] = arr;
				this.$emit('input', this.value);
				Vue.set(this.loading, va.id, false);
			}
		},
	},
	watch: {
		category () {
			this.search = '';
			this.vas = [];
			this.total = 'No';
			this.loaded = true;
		},
	},
	mounted () {
		this.search = '';
		this.vas = [];
		this.total = 'No';
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

@media (max-width: 1215.999px) {
	.va-picker-overflow-wrap {
		/* TODO hardcode bad */
		height: calc(100vh - 350px);
		overflow-y: auto;
	}
	.va-picker-entry {
		flex: 1 1 calc(100%/3);
	}
}

@media (max-width: 767px) {
	.va-picker-entry {
		flex: 1 1 100%;
	}
}
</style>
