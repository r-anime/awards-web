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
		<div v-else-if="!loaded" class="char-picker-text">
			Loading...
		</div>
		<div v-else class="char-picker-text">
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
			<label class="label">Character Roles:</label>
			<div class="control">
				<label class="radio">
					<input v-model="roles" type="radio" value="all"> All
				</label>
			</div>
			<div class="control">
				<label class="radio">
					<input v-model="roles" type="radio" value="main"> Main
				</label>
			</div>
			<div class="control">
				<label class="radio">
					<input v-model="roles" type="radio" value="supp"> Supporting
				</label>
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
			<label class="label">Character Roles:</label>
			<div class="control">
				<label class="radio">
					<input v-model="roles" type="radio" value="all"> All
				</label>
			</div>
			<div class="control">
				<label class="radio">
					<input v-model="roles" type="radio" value="main"> Main
				</label>
			</div>
			<div class="control">
				<label class="radio">
					<input v-model="roles" type="radio" value="supp"> Supporting
				</label>
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
          characterRole
        }
      }
      siteUrl
    }
  }
}
`;

const charImportQuery = `query ($page: Int, $start: FuzzyDateInt, $end: FuzzyDateInt, $charPage: Int) {
  anime: Page(page: $page, perPage: 50) {
    pageInfo {
      total
      perPage
      currentPage
      lastPage
      hasNextPage
    }
    results: media(type: ANIME, isAdult: false, countryOfOrigin: JP, endDate_greater: $start, endDate_lesser: $end) {
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
              first
              last
              full
              native
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
              first
              last
              full
              native
            }
          }
        }
      }
    }
  }
}`;

export default {
	components: {
		CharPickerEntry,
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
			chars: [],
			selections: [],
			total: 'No',
			selectedTab: 'selections',
			submitting: false,
			modalOpen: false,
			startDate: '2020-01-13',
			endDate: '2020-12-31',
			importing: false,
			roles: 'all',
			anilistIDs: '',
			importOpen: false,
		};
	},
	computed: {
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
				if (item.media.nodes[0].title.romaji) search = `${search}%${item.media.nodes[0].title.romaji}`;
				if (item.media.nodes[0].title.english) search = `${search}%${item.media.nodes[0].title.english}`;
				return {
					character_id: item.anilistID,
					anilist_id: item.media.nodes[0].id,
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
			const promise = new Promise((resolve, reject) => {
				try {
					this.chars = this.chars.filter(char => char.media.nodes.length > 0 && char.media.edges.length > 0);
					for (const char of this.chars) {
						char.anilistID = char.id;
					}
					resolve();
				} catch (error) {
					reject(error);
				}
			});
			promise.then(() => {
				this.total = this.chars.length;
				this.loaded = true;
			});
		},
		showSelected (char) {
			return this.selections.some(s => s.anilistID === char.anilistID);
		},
		toggleShow (char, select = true) {
			if (select) {
				if (this.showSelected(char)) return;
				this.selections.push(char);
			} else {
				if (!this.showSelected(char)) return;
				const index = this.selections.findIndex(c => c.id === char.id);
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
			for (const char of this.chars) {
				this.toggleShow(char, true);
			}
		},
		unselectAll () {
			for (const char of this.chars) {
				this.toggleShow(char, false);
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
				this.chars = [];
				for (const show of showData) {
					if (show.characters.edges.length === 0) continue;
					const mediaID = show.id;
					for (const char of show.characters.edges) {
						if (this.roles === 'main' && char.role === 'SUPPORTING' || this.roles === 'supp' && char.role === 'MAIN' || char.role === 'BACKGROUND' && this.roles !== 'all') {
							continue;
						}

						this.chars.push({
							id: char.id,
							anilistID: char.node.id,
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
				this.selectedTab = 'search';
				this.total = this.chars.length;
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
				this.chars = [];
				for (const show of showData) {
					if (show.characters.edges.length === 0) continue;
					const anime = show.title.romaji || show.title.english;
					const mediaID = show.id;
					for (const char of show.characters.edges) {
						if (this.roles === 'main' && char.role === 'SUPPORTING' || this.roles === 'supp' && char.role === 'MAIN' || char.role === 'BACKGROUND' && this.roles !== 'all' || this.chars.find(aChar => aChar.id === char.id)) {
							continue;
						}

						this.chars.push({
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
				this.selectedTab = 'search';
				this.total = this.chars.length;
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
				for (const char of charData) {
					char.anilistID = char.id;
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
.submit-wrapper {
	box-shadow: inset 0 1px #dbdbdb;
	text-align: center;
	padding: 5px;
}
</style>
