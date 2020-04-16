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
		<div v-else-if="selections.length" class="va-picker-overflow-wrap">
			<div class="va-picker-entries">
				<VAPickerEntry
					v-for="va in selections"
					:key="'selected' + va.id"
					:va="va"
					:selected="showSelected(va)"
					@action="toggleShow(va, $event)"
				/>
			</div>
		</div>
		<div v-else-if="!loaded" class="va-picker-text">
			Loading...
		</div>
		<div v-else class="va-picker-text">
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
import {mapActions} from 'vuex';
import VAPickerEntry from './VAPickerEntry';
const util = require('../../../util');
const aq = require('../../../anilistQueries');

const VASearchQuery = `query ($search: String) {
  character: Page(page: 1, perPage: 50) {
    pageInfo {
      total
    }
    results: characters(search: $search, sort: [SEARCH_MATCH]) {
      id
      name {
        full
      }
      image {
        large
      }
      media(sort: [END_DATE_DESC,START_DATE_DESC], type: ANIME, page: 1, perPage: 1) {
        nodes {
          id
          title {
            romaji
            english
          }
        }
        edges {
          id
          node {id}
          characterRole
          voiceActors (language: JAPANESE) {
            id
            name {
              full
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
		value: Array,
		category: Object,
	},
	data () {
		return {
			loaded: true,
			typingTimeout: null,
			search: '',
			vas: [],
			selections: [],
			total: 'No',
			selectedTab: 'selections',
			submitting: false,
		};
	},
	computed: {
		charIDs () {
			return this.value.map(show => show.character_id);
		},
		submissions () {
			return this.selections.map(item => ({
				character_id: item.id,
				categoryId: this.category.id,
			}));
		},
	},
	methods: {
		...mapActions([
			'updateEntries',
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
		async sendQuery () {
			if (!this.search) {
				this.loaded = true;
				this.vas = [];
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
					query: VASearchQuery,
					variables: {
						search: this.search,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.vas = data.data.character.results;
			const promise = new Promise((resolve, reject) => {
				try {
					this.vas = this.vas.filter(va => va.media.nodes.length > 0 && va.media.edges.length > 0);
					this.vas = this.vas.filter(va => va.media.edges[0].voiceActors.length > 0);
					resolve();
				} catch (error) {
					reject(error);
				}
			});
			promise.then(() => {
				this.total = this.vas.length;
				this.loaded = true;
			});
		},
		showSelected (va) {
			return this.selections.some(s => s.id === va.id);
		},
		toggleShow (va, select = true) {
			if (select) {
				if (this.showSelected(va)) return;
				this.selections.push(va);
			} else {
				if (!this.showSelected(va)) return;
				const index = this.selections.findIndex(c => c.id === va.id);
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
			for (const va of this.vas) {
				this.toggleShow(va, true);
			}
		},
		unselectAll () {
			for (const va of this.vas) {
				this.toggleShow(va, false);
			}
		},
		clear () {
			this.selections = [];
		},
	},
	mounted () {
		const charPromise = new Promise(async (resolve, reject) => {
			try {
				let charData = [];
				if (this.charIDs) {
					let lastPage = false;
					let page = 1;
					while (!lastPage) {
						// eslint-disable-next-line no-await-in-loop
						const returnData = await util.paginatedQuery(aq.charQuerySimple, this.charIDs, page);
						charData = [...charData, ...returnData.data.Page.results];
						lastPage = returnData.data.Page.pageInfo.currentPage === returnData.data.Page.pageInfo.lastPage;
						page++;
					}
				}
				resolve(charData);
			} catch (error) {
				reject(error);
			}
		});

		charPromise.then(charData => {
			this.selections = charData;
			this.loaded = true;
		});
	},
};
</script>

<style lang="scss">
.tabs.va-picker-tabs {
	margin-bottom: 0 !important;
}
.va-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 141px - 46px);
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
.submit-wrapper {
	box-shadow: inset 0 1px #dbdbdb;
	text-align: center;
	padding: 5px;
}
</style>
