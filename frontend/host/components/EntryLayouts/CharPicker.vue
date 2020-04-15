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
					:selected="showSelected(char)"
					@action="toggleShow(char, $event)"
				/>
			</div>
			<div v-else-if="loaded" class="char-picker-text">
				{{search ? 'No results :(' : ''}}
			</div>
			<div v-else class="char-picker-text">
				Loading...
			</div>
		</div>
		<div v-else-if="selections.length && loaded" class="char-picker-overflow-wrap">
			<div class="char-picker-entries">
				<char-picker-entry
					v-for="char in selections"
					:key="'selected' + char.id"
					:char="char"
					:selected="showSelected(char)"
					@action="toggleShow(char, $event)"
				/>
			</div>
		</div>
		<div v-else-if="loaded" class="char-picker-text">
			Loading...
		</div>
		<div v-else class="char-picker-text">
			You don't have any selections in this category yet. Get started on the search tab.
		</div>
	</div>
</template>

<script>
import CharPickerEntry from './CharPickerEntry';
const util = require('../../../util');
const aq = require('../../../anilistQueries');

const charSearchQuery = `query ($search: String) {
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
            userPreferred
          }
        }
        edges {
          id
          characterRole
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
		value: Array,
		category: Object,
	},
	data () {
		return {
			loaded: true,
			typingTimeout: null,
			search: '',
			chars: [],
			selections: [],
			total: 'No',
			selectedTab: 'selections',
		};
	},
	computed: {
		charIDs () {
			return this.value.map(show => show.character_id);
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
					query: charSearchQuery,
					variables: {
						search: this.search,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.chars = data.data.character.results;
			this.total = data.data.character.pageInfo.total || 'No';
			this.loaded = true;
		},
		showSelected (char) {
			return this.selections.some(s => s.id === char.id);
		},
		toggleShow (char, select = true) {
			if (select) {
				if (this.showSelected(char)) return;
				this.selections.push(char);
				this.$emit('input', [...this.value, {character_id: char.id, categoryId: this.category.id}]);
			} else {
				if (!this.showSelected(char)) return;
				let index = this.selections.findIndex(c => c.id === char.id);
				const arr = [...this.value];
				this.selections.splice(index, 1);
				index = this.value.findIndex(s => s.character_id === char.id);
				arr.splice(index, 1);
				this.$emit('input', arr);
			}
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
.tabs.char-picker-tabs {
	margin-bottom: 0 !important;
}
.char-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 141px - 46px);
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
