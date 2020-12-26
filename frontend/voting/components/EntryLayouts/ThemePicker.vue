<!--This one pulls music from the DB and stuff. Should be straightforward.-->
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
/* eslint-disable no-alert */

import Vue from 'vue';
import ThemePickerEntry from './ThemePickerEntry';
import {mapActions, mapState} from 'vuex';
import Fuse from 'fuse.js/dist/fuse.basic.min';
const util = require('../../../util');

const showPaginatedQuery = `query ($id: [Int], $page: Int, $perPage: Int) {
  Page(page: $page, perPage: $perPage) {
    pageInfo {
      lastPage
    }
    results: media(type: ANIME, id_in: $id) {
      id
      title {
        romaji
        english
      }
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
		'title',
		'anime',
	],
};

export default {
	components: {
		ThemePickerEntry,
	},
	props: {
		value: Object,
		category: Object,
	},
	computed: {
		...mapState([
			'themes',
			'selections',
		]),
		showIDs () {
			return this.themes.filter(theme => theme.themeType.toUpperCase() === this.category.name).map(show => show.anilistID);
		},
		maxNoms () {
			return this.value[this.category.id].length >= 10;
		},
		isLoading () {
			return this.loading.includes(true);
		},
	},
	data () {
		return {
			loaded: false,
			typingTimeout: null,
			search: '',
			shows: [],
			backup: [],
			total: 'No',
			loading: [],
		};
	},
	methods: {
		...mapActions(['getThemes']),
		handleInput (event) {
			// TODO - this could just be a watcher
			this.search = event.target.value;
			this.loaded = false;
			clearTimeout(this.typingTimeout);
			this.typingTimeout = setTimeout(() => {
				this.searchThemes();
			}, 750);
		},
		searchThemes () {
			if (!this.search) {
				this.shows = this.backup;
				this.total = this.shows.length;
				this.loaded = true;
				return;
			}
			const entries = this.backup;
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
				if (this.value[this.category.id].length >= 10) {
					alert('You cannot vote for any more entries.');
					Vue.set(this.loading, show.id, false);
					return;
				}
				const themeIndex = this.value[this.category.id].findIndex(theme => theme.title === show.title);
				if (themeIndex !== -1) { // If we find it...
					// Confirm that the user wants to move their vote
					if (confirm('You have already selected another version of this theme. Do you want to move your vote to this version.')) {
						// If they want to move it, we need to update the entry in the other category
						const response = await fetch('/api/votes/delete', {
							method: 'POST',
							body: JSON.stringify({
								category_id: this.category.id,
								entry_id: show.id,
								anilist_id: show.anilistID,
								theme_name: show.title,
							}),
						});
						if (!response.ok) {
							// eslint-disable-next-line no-alert
							alert('Something went wrong submitting your selection');
							Vue.set(this.loading, show.id, false);
							return;
						}
						this.value[this.category.id].splice(themeIndex, 1);
						this.$emit('input', this.value);
					} else {
						// If they cancel out, return
						Vue.set(this.loading, show.id, false);
						return;
					}
				}
				const response = await fetch('/api/votes/submit', {
					method: 'POST',
					body: JSON.stringify({
						category_id: this.category.id,
						entry_id: show.id,
						anilist_id: show.anilistID,
						theme_name: show.title,
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
						anilist_id: show.anilistID,
						theme_name: show.title,
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
	async mounted () {
		if (!this.themes) {
			await this.getThemes();
		}
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
				this.themes.forEach(element => {
					const requiredShow = showData.find(show => show.id === element.anilistID);
					this.backup.push({...requiredShow, ...element});
				});
				this.shows = this.backup;
				this.loaded = true;
			});
		} else {
			this.loaded = true;
		}
	},
	watch: {
		async category () {
			if (!this.themes) {
				await this.getThemes();
			}
			this.search = '';
			this.shows = [];
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
					this.themes.forEach(element => {
						const requiredShow = showData.find(show => show.id === element.anilistID);
						this.backup.push({...requiredShow, ...element});
					});
					this.shows = this.backup;
					this.loaded = true;
				});
			} else {
				this.loaded = true;
			}
		},
	},
};
</script>

<style lang="scss">
.tabs.show-picker-tabs {
	margin-bottom: 0 !important;
}
.show-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 310px);
	overflow-y: auto;
}
@media (max-width: 1215.999px) {
	.show-picker-overflow-wrap {
		/* TODO hardcode bad */
		height: calc(100vh - 350px);
		overflow-y: auto;
	}
}


.show-picker-overflow-wrap::-webkit-scrollbar {
    width: 8px;
}

.show-picker-overflow-wrap::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.7);
    border-radius: 20px;
}

.show-picker-overflow-wrap::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 20px;
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
</style>
