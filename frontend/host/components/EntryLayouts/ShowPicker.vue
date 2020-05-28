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
							{{total}} show{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && shows.length" class="show-picker-entries">
				<show-picker-entry
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
		<div v-else-if="selections.length && loaded" class="show-picker-overflow-wrap">
			<div class="show-picker-entries">
				<show-picker-entry
					v-for="show in selections"
					:key="'selected' + show.id"
					:show="show"
					:selected="showSelected(show)"
					@action="toggleShow(show, $event)"
				/>
			</div>
		</div>
		<div v-else-if="!loaded" class="show-picker-text">
			Loading...
		</div>
		<div v-else class="show-picker-text">
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
			<label class="label">Formats:</label>
			<div class="control">
				<label class="checkbox">
					<input v-model="formats" value="TV" type="checkbox"> TV Anime
				</label>
				<label class="checkbox">
					<input v-model="formats" value="TV_SHORT" type="checkbox"> TV Shorts
				</label>
				<label class="checkbox">
					<input v-model="formats" value="MOVIE" type="checkbox"> Movies
				</label>
				<label class="checkbox">
					<input v-model="formats" value="OVA" type="checkbox"> OVAs
				</label>
				<label class="checkbox">
					<input v-model="formats" value="ONA" type="checkbox"> ONAs
				</label>
				<label class="checkbox">
					<input v-model="formats" value="SPECIAL" type="checkbox"> Specials
				</label>
				<label class="checkbox">
					<input v-model="formats" value="MUSIC" type="checkbox"> MVs
				</label>
			</div>
		</div>
		<div class="field">
			<div class="control">
				<button @click="importShows" class="button is-primary" :class="{'is-loading': importing}">Import</button>
			</div>
		</div>
		</modal-generic>
	</div>
</template>

<script>
import ModalGeneric from '../ModalGeneric';
import {mapActions} from 'vuex';
import ShowPickerEntry from './ShowPickerEntry';
const util = require('../../../util');
const aq = require('../../../anilistQueries');

const showSearchQuery = `
    query ($search: String) {
        anime: Page (page: 1, perPage: 50) {
            pageInfo {
                total
            }
            results: media (type: ANIME, search: $search) {
                id
                format
                startDate {
                    year
                }
                title {
                    romaji
                    english
                    native
                    userPreferred
                }
                coverImage {
                    large
                }
                siteUrl
            }
        }
    }
`;

const showImportQuery = `query ($page: Int, $formats: [MediaFormat], $start: FuzzyDateInt, $end: FuzzyDateInt) {
	anime: Page(page: $page, perPage: 50) {
	  pageInfo {
		total
		perPage
		currentPage
		lastPage
		hasNextPage
	  }
	  results: media(type: ANIME, isAdult: false, format_in: $formats, countryOfOrigin: JP, endDate_greater: $start, endDate_lesser: $end) {
		id
		format
		startDate {
		  year
		}
		title {
		  romaji
		  english
		  native
		  userPreferred
		}
		coverImage {
		  large
		}
		siteUrl
	  }
	}
  }
  `;

export default {
	components: {
		ShowPickerEntry,
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
			shows: [],
			selections: [],
			total: 'No',
			selectedTab: 'selections',
			submitting: false,
			modalOpen: false,
			startDate: '2020-01-13',
			endDate: '2021-01-12',
			formats: [],
			importing: false,
		};
	},
	computed: {
		showIDs () {
			return this.value.map(show => show.anilist_id);
		},
		submissions () {
			return this.selections.map(item => ({
				anilist_id: item.id,
				character_id: null,
				themeId: null,
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
				this.shows = [];
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
					query: showSearchQuery,
					variables: {
						search: this.search,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.shows = data.data.anime.results;
			this.total = data.data.anime.pageInfo.total || 'No';
			this.loaded = true;
		},
		showSelected (show) {
			return this.selections.some(s => s.id === show.id);
		},
		toggleShow (show, select = true) {
			if (select) {
				if (this.showSelected(show)) return;
				this.selections.push(show);
			} else {
				if (!this.showSelected(show)) return;
				const index = this.selections.findIndex(s => s.id === show.id);
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
			for (const show of this.shows) {
				this.toggleShow(show, true);
			}
		},
		unselectAll () {
			for (const show of this.shows) {
				this.toggleShow(show, false);
			}
		},
		clear () {
			this.selections = [];
		},
		fuzzyDate (date) {
			const dateArr = date.split('-');
			return Number(`${dateArr[0]}${dateArr[1]}${dateArr[2]}`);
		},
		async importQuery (query, page, formats, start, end) {
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
						formats,
						start,
						end,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			return data;
		},
		importShows () {
			this.importing = true;
			const showPromise = new Promise(async (resolve, reject) => {
				try {
					let showData = [];
					let lastPage = false;
					let page = 1;
					while (!lastPage) {
						// eslint-disable-next-line no-await-in-loop
						const returnData = await this.importQuery(showImportQuery, page, this.formats, this.fuzzyDate(this.startDate), this.fuzzyDate(this.endDate));
						showData = [...showData, ...returnData.data.anime.results];
						lastPage = returnData.data.anime.pageInfo.currentPage === returnData.data.anime.pageInfo.lastPage;
						page++;
					}
					resolve(showData);
				} catch (error) {
					reject(error);
				}
			});
			showPromise.then(showData => {
				this.shows = showData;
				this.selectedTab = 'search';
				this.total = this.shows.length;
				this.loaded = true;
				this.importing = false;
				this.modalOpen = false;
			});
		},
	},
	async mounted () {
		const promiseArray = [];
		let showData = [];
		if (this.showIDs) {
			let page = 1;
			const someData = await util.paginatedQuery(aq.showQuerySimple, this.showIDs, page);
			showData = [...showData, ...someData.data.Page.results];
			const lastPage = someData.data.Page.pageInfo.lastPage;
			page = 2;
			while (page < lastPage) {
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
				this.selections = showData;
				this.loaded = true;
			});
		}
		this.loaded = true;
	},
};
</script>

<style lang="scss">
.tabs.show-picker-tabs {
	margin-bottom: 0 !important;
}
.show-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 141px - 46px);
	overflow-y: auto;
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
.submit-wrapper {
	box-shadow: inset 0 1px #dbdbdb;
	text-align: center;
	padding: 5px;
}
</style>
