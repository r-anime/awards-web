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
							{{total}} theme{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && shows.length" class="show-picker-entries">
				<ThemePickerEntry
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
		<div v-else-if="selections.length" class="show-picker-overflow-wrap">
			<div class="show-picker-entries">
				<ThemePickerEntry
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
		</div>
	</div>
</template>

<script>
import ThemePickerEntry from './ThemePickerEntry';
import {mapActions, mapState} from 'vuex';
const Fuse = require('fuse.js');
const util = require('../../../util');
const aq = require('../../../anilistQueries');

const options = {
	shouldSort: true,
	threshold: 0.3,
	location: 0,
	distance: 70,
	maxPatternLength: 64,
	minMatchCharLength: 3,
	keys: [
		'title',
		'anime',
	],
};

const themeSearchQuery = `query ($id: [Int]) {
  Page {
    media(id_in: $id) {
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
		ThemePickerEntry,
	},
	props: {
		value: Array,
		category: Object,
	},
	computed: {
		...mapState([
			'themes',
		]),
		showIDs () {
			return this.value.map(show => show.anilist_id);
		},
		submissions () {
			return this.selections.map(item => ({
				themeId: item.id,
				categoryId: this.category.id,
				anilist_id: item.anilistID,
				character_id: null,
			}));
		},
	},
	data () {
		return {
			loaded: false,
			typingTimeout: null,
			search: '',
			shows: [],
			showData: [],
			themeData: [],
			selections: [],
			total: 'No',
			selectedTab: 'selections',
			idArr: [],
			submitting: false,
		};
	},
	methods: {
		...mapActions([
			'getThemes',
			'updateEntries',
		]),
		handleInput (event) {
			// TODO - this could just be a watcher
			this.search = event.target.value;
			this.loaded = false;
			clearTimeout(this.typingTimeout);
			this.typingTimeout = setTimeout(() => {
				this.searchThemes();
			}, 750);
		},
		async sendQuery () {
			const response = await fetch('https://graphql.anilist.co', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query: themeSearchQuery,
					variables: {
						id: this.idArr,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.showData = data.data.Page.media;
			this.loaded = true;
		},
		async searchThemes () {
			if (!this.search) {
				this.loaded = true;
				this.themeData = [];
				this.total = 'No';
				return;
			}
			const fuse = new Fuse(this.themes, options);
			this.themeData = fuse.search(this.search);
			this.total = this.themeData.length;
			this.themeData.forEach(element => {
				this.idArr.push(element.anilistID);
			});
			await this.sendQuery();
			this.squashObjects();
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
				const index = this.selections.findIndex(s => s.themeId === show.id);
				this.selections.splice(index, 1);
			}
		},
		requiredShowData (index) {
			const found = this.showData.find(show => show.id === this.themeData[index].anilistID);
			return found;
		},
		// I hate what I'm about to do here
		squashObjects () {
			this.themeData.forEach((element, index) => {
				const fetchData = this.requiredShowData(index);
				this.shows.push({...fetchData, ...element});
			});
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
	},
	mounted () {
		if (!this.themes) {
			this.getThemes();
		}
		const showPromise = new Promise(async (resolve, reject) => {
			try {
				let showData = [];
				if (this.showIDs) {
					let lastPage = false;
					let page = 1;
					while (!lastPage) {
						// eslint-disable-next-line no-await-in-loop
						const returnData = await util.paginatedQuery(aq.showQuerySimple, this.showIDs, page);
						showData = [...showData, ...returnData.data.Page.results];
						lastPage = returnData.data.Page.pageInfo.currentPage === returnData.data.Page.pageInfo.lastPage;
						page++;
					}
				}
				resolve(showData);
			} catch (error) {
				reject(error);
			}
		});

		showPromise.then(showData => {
			this.value.forEach(element => {
				const requiredTheme = this.themes.find(theme => theme.id === element.themeId);
				const requiredShow = showData.find(show => show.id === element.anilist_id);
				this.selections.push({...requiredShow, ...requiredTheme});
			});
			this.loaded = true;
		});
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
