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
				<li>
					<input v-model="page" class="input" type="number">
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
		<div v-else-if="selections.length && loaded" class="va-picker-overflow-wrap">
			<div class="va-picker-entries">
				<VAPickerEntry
					v-for="va in filteredVAs"
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
			<button
				class="button is-primary"
				@click="modalOpen = true"
			>
				Batch Import
			</button>
			<button
				class="button is-primary"
				@click="importOpen = true"
			>
				AniList ID Import
			</button>
		</div>

		<modal-generic v-model="modalOpen">
		<div class="field">
			<label class="label">Eligibility Start:</label>
			<div class="control">
				<input v-model="startDate" class="input" type="date">
			</div>
		</div>
		<div class="field">
			<label class="label">Eligibility End:</label>
			<div class="control">
				<input v-model="endDate" class="input" type="date">
			</div>
		</div>
		<div class="field">
			<div class="control">
				<button @click="importShows" class="button is-primary" :class="{'is-loading': importing}">Import</button>
			</div>
		</div>
		</modal-generic>

		<modal-generic v-model="importOpen">
		<div class="field">
			<label class="label">Line seperated AniList IDs of shows:</label>
			<div class="control">
				<textarea v-model="anilistIDs" class="textarea" placeholder="Line separated anilist IDs of shows here"/>
			</div>
		</div>
		<div class="field">
			<div class="control">
				<button @click="importShowsFromIDs" class="button is-primary" :class="{'is-loading': importing}">Import</button>
			</div>
		</div>
		</modal-generic>
	</div>
</template>

<script>
import ModalGeneric from '../../../common/ModalGeneric';
import {mapActions} from 'vuex';
import VAPickerEntry from './VAPickerEntry';
const util = require('../../../util');
const aq = require('../../../anilistQueries');
const constants = require('../../../../constants');

const VASearchQuery = `query ($search: String) {
  character: Page(page: 1, perPage: 50) {
    pageInfo {
      total
    }
    results: characters(search: $search, sort: [SEARCH_MATCH]) {
      id
      name {
		first
		last
		full
		native
      }
      image {
        large
      }
      media(sort: [START_DATE], type: ANIME, page: 1, perPage: 1) {
        nodes {
          id
          title {
            romaji
			english
			native
			userPreferred
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

const charImportQuery = `query ($page: Int, $start: FuzzyDateInt, $end: FuzzyDateInt, $charPage: Int, $exclusions: [Int]) {
  anime: Page(page: $page, perPage: 50) {
    pageInfo {
      total
      perPage
      currentPage
      lastPage
      hasNextPage
    }
    results: media(type: ANIME, id_not_in: $exclusions, isAdult: false, countryOfOrigin: JP, endDate_greater: $start, endDate_lesser: $end) {
      id
      title {
        romaji
        english
      }
      characters(page: $charPage, perPage: 50) {
        pageInfo {
          total
          perPage
          currentPage
          lastPage
          hasNextPage
        }
        edges {
          id
          node {
            id
            name {
			  full
			  alternative
            }
            siteUrl
            image {
              large
              medium
            }
          }
          role
          voiceActors(language: JAPANESE) {
            id
            name {
              full
            }
          }
        }
      }
    }
  }
}
`;

const importFromIDsQuery = `query ($page: Int,$charPage: Int, $id:[Int]) {
  anime: Page(page: $page, perPage: 50) {
    pageInfo {
      total
      perPage
      currentPage
      lastPage
      hasNextPage
    }
    results: media(type: ANIME, isAdult: false, countryOfOrigin: JP, id_in:$id) {
      id
      title {
        romaji
        english
        native
        userPreferred
      }
      characters(page: $charPage, perPage: 50) {
        pageInfo {
          total
          perPage
          currentPage
          lastPage
          hasNextPage
        }
        edges {
          id
          node {
            id
            name {
			  full
			  alternative
            }
            siteUrl
            image {
              large
              medium
            }
          }
          role
          voiceActors(language: JAPANESE) {
            id
            name {
              full
            }
          }
        }
      }
    }
  }
}`;

export default {
	components: {
		VAPickerEntry,
		ModalGeneric,
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
			vas: [],
			selections: [],
			total: 'No',
			selectedTab: 'selections',
			submitting: false,
			modalOpen: false,
			startDate: '2020-01-13',
			endDate: '2020-12-31',
			importing: false,
			anilistIDs: '',
			importOpen: false,
			page: 0,
		};
	},
	computed: {
		filteredVAs () {
			const _page = parseInt(this.page, 10);
			const filtered = this.selections.filter((el, i) => i >= _page * 100 && i < (_page + 1) * 100);
			// console.log(this.page * 50, (this.page + 1) * 50);
			return filtered;
		},
		charIDs () {
			return this.value.map(show => show.character_id);
		},
		submissions () {
			return this.selections.map(item => {
				let search = `${item.name.full}`;
				if (item.name.alternative) {
					for (const altname of item.name.alternative) {
						search = `${search}%${altname}`;
					}
				}
				if (item.media.edges.length) {
					if (item.media.edges[0].voiceActors.length) {
						if (item.media.edges[0].voiceActors[0].name.full) {
							search = `${search}%${item.media.edges[0].voiceActors[0].name.full}`;
						}
					}
				}
				if (item.media.nodes.length) {
					if (item.media.nodes[0].title.romaji) search = `${search}%${item.media.nodes[0].title.romaji}`;
					if (item.media.nodes[0].title.english) search = `${search}%${item.media.nodes[0].title.english}`;
				}
				return {
					character_id: item.id,
					anilist_id: item.media.nodes.length ? item.media.nodes[0].id : null,
					themeId: null,
					categoryId: this.category.id,
					search,
				};
			});
		},
		anilistIDArr () {
			return this.anilistIDs.split('\n');
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
				this.vas = [];
				this.total = 'No';
				this.loaded = true;
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
		fuzzyDate (date) {
			const dateArr = date.split('-');
			return Number(`${dateArr[0]}${dateArr[1]}${dateArr[2]}`);
		},
		async importQuery (query, page, start, end, charPage) {
			const response = await fetch('https://graphql.anilist.co', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query,
					variables: {
						page,
						charPage,
						start,
						end,
						exclusions: constants.exclusions,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			return data;
		},
		async importFromIDsQuery (page, charPage) {
			const response = await fetch('https://graphql.anilist.co', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query: importFromIDsQuery,
					variables: {
						// eslint-disable-next-line radix
						id: this.anilistIDArr,
						page,
						charPage,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			return data;
		},
		importShowsFromIDs () {
			this.importing = true;
			const charPromise = new Promise(async (resolve, reject) => {
				try {
					let showData = [];
					let lastPage = false;
					let nextCharaPage = false;
					let page = 1;
					let charPage = 1;
					while (!lastPage) {
						// eslint-disable-next-line no-await-in-loop
						const returnData = await this.importFromIDsQuery(page, charPage);
						// check if any anime here have a next page for charas
						nextCharaPage = returnData.data.anime.results.some(item => item.characters.pageInfo.hasNextPage);
						while (nextCharaPage) {
							charPage++;
							// eslint-disable-next-line no-await-in-loop
							const nextData = await this.importFromIDsQuery(page, charPage);
							nextData.data.anime.results = nextData.data.anime.results.filter(item => item.characters.edges.length > 0);
							for (const item of nextData.data.anime.results) {
								const index = returnData.data.anime.results.findIndex(anime => anime.id === item.id);
								returnData.data.anime.results[index].characters.edges = [...returnData.data.anime.results[index].characters.edges, ...item.characters.edges];
							}
							nextCharaPage = nextData.data.anime.results.some(item => item.characters.pageInfo.hasNextPage);
						}
						charPage = 1;
						nextCharaPage = false;
						showData = [...showData, ...returnData.data.anime.results];
						lastPage = returnData.data.anime.pageInfo.currentPage === returnData.data.anime.pageInfo.lastPage;
						page++;
					}
					resolve(showData);
				} catch (error) {
					reject(error);
				}
			});
			charPromise.then(showData => {
				this.vas = [];
				for (const show of showData) {
					if (show.characters.edges.length === 0) continue;
					const anime = show.title.romaji || show.title.english;
					const mediaID = show.id;
					for (const char of show.characters.edges) {
						if (char.role === 'BACKGROUND' || this.vas.find(va => va.id === char.id)) continue;
						this.vas.push({
							id: char.id,
							anilistID: char.node.id,
							name: char.node.name,
							image: char.node.image,
							siteUrl: char.node.siteUrl,
							media: {
								nodes: [{
									id: mediaID,
									title: {
										romaji: anime,
									},
								}],
								edges: [{
									id: char.id,
									characterRole: char.role,
									voiceActors: char.voiceActors,
								}],
							},
						});
					}
				}
				this.vas = this.vas.filter(va => va.media.edges[0].voiceActors.length > 0);
				this.selectedTab = 'search';
				this.total = this.vas.length;
				this.loaded = true;
				this.importing = false;
				this.importOpen = false;
			});
		},
		importShows () {
			this.importing = true;
			const charPromise = new Promise(async (resolve, reject) => {
				try {
					let showData = [];
					let lastPage = false;
					let nextCharaPage = false;
					let page = 1;
					let charPage = 1;
					while (!lastPage) {
						// eslint-disable-next-line no-await-in-loop
						const returnData = await this.importQuery(charImportQuery, page, this.fuzzyDate(this.startDate), this.fuzzyDate(this.endDate), charPage);
						// check if any anime here have a next page for charas
						nextCharaPage = returnData.data.anime.results.some(item => item.characters.pageInfo.hasNextPage);
						while (nextCharaPage) {
							charPage++;
							// eslint-disable-next-line no-await-in-loop
							const nextData = await this.importQuery(charImportQuery, page, this.fuzzyDate(this.startDate), this.fuzzyDate(this.endDate), charPage);
							nextData.data.anime.results = nextData.data.anime.results.filter(item => item.characters.edges.length > 0);
							for (const item of nextData.data.anime.results) {
								const index = returnData.data.anime.results.findIndex(anime => anime.id === item.id);
								returnData.data.anime.results[index].characters.edges = [...returnData.data.anime.results[index].characters.edges, ...item.characters.edges];
							}
							nextCharaPage = nextData.data.anime.results.some(item => item.characters.pageInfo.hasNextPage);
						}
						charPage = 1;
						nextCharaPage = false;
						showData = [...showData, ...returnData.data.anime.results];
						lastPage = returnData.data.anime.pageInfo.currentPage === returnData.data.anime.pageInfo.lastPage;
						page++;
					}
					resolve(showData);
				} catch (error) {
					reject(error);
				}
			});
			charPromise.then(showData => {
				this.vas = [];
				for (const show of showData) {
					if (show.characters.edges.length === 0) continue;
					const mediaID = show.id;
					for (const char of show.characters.edges) {
						if (char.voiceActors.length === 0 || this.vas.find(aChar => aChar.id === char.node.id)) continue;
						this.vas.push({
							id: char.node.id,
							name: char.node.name,
							image: char.node.image,
							siteUrl: char.node.siteUrl,
							media: {
								nodes: [{
									id: mediaID,
									title: {
										romaji: show.title.romaji,
										english: show.title.english,
									},
								}],
								edges: [{
									id: char.id,
									characterRole: char.role,
									voiceActors: char.voiceActors,
								}],
							},
						});
					}
				}
				this.vas = this.vas.filter(va => va.media.edges[0].voiceActors.length > 0);
				this.selectedTab = 'search';
				this.total = this.vas.length;
				this.loaded = true;
				this.importing = false;
				this.modalOpen = false;
			});
		},
	},
	async mounted () {
		const promiseArray = [];
		let charData = [];
		if (this.charIDs.length) {
			let page = 1;
			const someData = await util.paginatedQuery(aq.charQuerySimple, this.charIDs, page);
			charData = [...charData, ...someData.data.Page.results];
			const lastPage = someData.data.Page.pageInfo.lastPage;
			page = 2;
			while (page <= lastPage) {
				// eslint-disable-next-line no-loop-func
				promiseArray.push(new Promise(async (resolve, reject) => {
					try {
						const returnData = await util.paginatedQuery(aq.charQuerySimple, this.charIDs, page);
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
				this.selections = charData;
				this.loaded = true;
			});
		} else {
			this.loaded = true;
		}
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
