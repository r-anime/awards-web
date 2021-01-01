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
							:disabled="lockSearch"
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
							{{total}} show{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && shows.length" class="show-picker-entries" @scroll="handleScroll($event)">
				<show-picker-entry
					v-for="show in shows"
					:key="show.id"
					:show="show"
					:selected="showSelected(show)"
					:loading="isLoading || (!showSelected(show) && maxNoms)"
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
	</div>
</template>

<script>
/* eslint-disable vue/no-mutating-props */
import Vue from 'vue';
import ShowPickerEntry from './ShowPickerEntry';
import Fuse from 'fuse.js/dist/fuse.basic.min';
const util = require('../../../util');

const showPaginatedQuery = `query ($id: [Int], $page: Int, $perPage: Int) {
  Page(page: $page, perPage: $perPage) {
    pageInfo {
      lastPage
    }
    results: media(type: ANIME, id_in: $id) {
      id
      format
      title {
        romaji
        english
      }
      synonyms
      coverImage {
        large
      }
      siteUrl
      idMal
    }
  }
}`;

const options = {
	shouldSort: true,
	threshold: 0.3,
	location: 0,
	distance: 70,
	maxPatternLength: 64,
	minMatchCharLength: 3,
	keys: [
		'title.romaji',
		'title.english',
		'synonyms',
	],
};

export default {
	components: {
		ShowPickerEntry,
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
			shows: null,
			total: 'No',
			showData: null,
			loading: [],
			lockSearch: false,
		};
	},
	computed: {
		showIDs () {
			return this.entries.map(show => show.anilist_id);
		},
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
		sendQuery () {
			if (!this.search) {
				this.shows = this.showData;
				this.total = this.shows.length;
				this.loaded = true;
				return;
			}
			const entries = this.showData;
			const fuse = new Fuse(entries, options);
			this.shows = fuse.search(this.search);
			this.shows = this.shows.map(show => show.item);
			this.total = this.shows.length;
			this.loaded = true;
		},
		showSelected (show) {
			return this.value[this.category.id].some(s => s.id === show.id);
		},
		async toggleShow (show, select = true) {
			Vue.set(this.loading, show.id, true);
			if (select) {
				if (this.showSelected(show)) {
					Vue.set(this.loading, show.id, false);
					return;
				}
				// Limit number of nominations
				if (this.maxNoms) {
					// eslint-disable-next-line no-alert
					alert('You cannot vote for any more entries.');
					Vue.set(this.loading, show.id, false);
					return;
				}
				const response = await fetch('/api/votes/submit', {
					method: 'POST',
					body: JSON.stringify({
						category_id: this.category.id,
						entry_id: show.id,
						anilist_id: null,
						theme_name: null,
					}),
				});
				if (!response.ok) {
					// eslint-disable-next-line no-alert
					alert('Something went wrong submitting your selection');
					Vue.set(this.loading, show.id, false);
					return;
				}
				this.value[this.category.id].push(show);
				this.$emit('input', this.value);
				Vue.set(this.loading, show.id, false);
			} else {
				if (!this.showSelected(show)) return;
				const index = this.value[this.category.id].findIndex(s => s.id === show.id);
				const arr = [...this.value[this.category.id]];
				arr.splice(index, 1);
				const response = await fetch('/api/votes/delete', {
					method: 'POST',
					body: JSON.stringify({
						category_id: this.category.id,
						entry_id: show.id,
						anilist_id: null,
						theme_name: null,
					}),
				});
				if (!response.ok) {
					// eslint-disable-next-line no-alert
					alert('Something went wrong submitting your selection');
					return;
				}
				this.value[this.category.id] = arr;
				this.$emit('input', this.value);
				Vue.set(this.loading, show.id, false);
			}
		},
	},
	watch: {
		async category () {
			this.total = 'Loading';
			this.lockSearch = true;
			this.loaded = false;
			this.search = '';
			const promiseArray = [];
			let showData = [];
			if (this.showIDs) {
				let page = 1;
				const someData = await util.paginatedQuery(showPaginatedQuery, this.showIDs, page);
				showData = [...showData, ...someData.data.Page.results];
				const lastPage = someData.data.Page.pageInfo.lastPage;
				page = 2;
				while (page <= lastPage) {
					// eslint-disable-next-line no-loop-func
					promiseArray.push(new Promise(async (resolve, reject) => {
						try {
							const returnData = await util.paginatedQuery(showPaginatedQuery, this.showIDs, page);
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
					this.showData = util.shuffle(showData);
					this.shows = showData;
					this.total = this.shows.length;
					this.lockSearch = false;
					this.loaded = true;
				});
			} else {
				this.total = 0;
				this.lockSearch = false;
				this.loaded = true;
			}
		},
	},
	async mounted () {
		this.total = 'Loading';
		const promiseArray = [];
		let showData = [];
		if (this.showIDs) {
			let page = 1;
			const someData = await util.paginatedQuery(showPaginatedQuery, this.showIDs, page);
			showData = [...showData, ...someData.data.Page.results];
			const lastPage = someData.data.Page.pageInfo.lastPage;
			page = 2;
			while (page <= lastPage) {
				// eslint-disable-next-line no-loop-func
				promiseArray.push(new Promise(async (resolve, reject) => {
					try {
						const returnData = await util.paginatedQuery(showPaginatedQuery, this.showIDs, page);
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
				this.showData = util.shuffle(showData);
				this.shows = showData;
				this.total = this.shows.length;
				this.loaded = true;
			});
		} else {
			this.total = 0;
			this.lockSearch = false;
			this.loaded = true;
		}
	},
};
</script>
