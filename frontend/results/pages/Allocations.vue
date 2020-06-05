<template>
    <div class="has-background-dark">
        <div class="container">
            <div class="columns is-centered">
                <div class="column is-10-fullhd is-11-widescreen is-12-desktop is-12-tablet">
                    <section class="section">
                        <h1 class="title is-2 has-text-gold has-text-centered has-flaired-underline pb-20">Category Allocations</h1>
                        <div v-if="locked" class="has-text-light has-text-centered">
                            <section class="hero is-fullheight-with-navbar section has-background-dark">
                                <h2 class="title is-3">You cannot see allocations at this time.</h2>
                            </section>
                        </div>
                        <div v-else-if="loaded" class="columns is-centered">
                            <div class="column is-2">
                                <nav class="panel has-background-periwinkle is-platinum">
                                    <p class="panel-heading has-text-light has-text-centered">Categories</p>
                                    <AllocationLink
									v-for="category in filteredCategories"
									:key="category.id"
									@input="changeCategory(category)"
									:name="category.name"
									v-model="selectedCategory"
									/>
                                </nav>
                            </div>
                            <div class="column is-10">
                                <div class="show-picker">
                                    <div class="show-picker-overflow-wrap">
                                        <div class="show-picker-search-bar">
                                            <div class="field has-addons">
                                                <p class="control has-icons-left is-expanded">
                                                    <input class="input is-small" type="text" :value="search" @input="handleInput($event)" placeholder="Search by title..." />
                                                    <span class="icon is-small is-left">
                                                        <i class="fas fa-search" />
                                                    </span>
                                                </p>
                                                <div class="control">
                                                    <span class="button is-small is-static" :class="{'is-loading': !fetched}">
                                                        {{total}} show{{total === 1 ? '' : 's'}}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="fetched && shows.length" class="show-picker-entries">
                                            <AllocationCard v-for="show in shows" :key="show.id" :show="show"/>
                                        </div>
                                        <div v-else-if="fetched" class="show-picker-text">
                                            {{search ? 'No results :(' : ''}}
                                        </div>
										<div v-else class="show-picker-text has-text-light">
											Loading...
										</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <section class="hero is-fullheight-with-navbar section has-background-dark" v-else>
                            <div class="container">
                                <div class="columns is-desktop is-vcentered">
                                    <div class="column is-9-fullhd is-10-widescreen is-11-desktop is-12-mobile">
                                        <div class="section">
                                            <div class="loader is-loading"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
import {mapState, mapActions} from 'vuex';
import AllocationCard from '../components/AllocationCard';
import AllocationLink from '../components/AllocationLink';
const Fuse = require('fuse.js');
const util = require('../../util');
const aq = require('../../anilistQueries');

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
		'title.native',
		'title.userPreferred',
	],
};

export default {
	components: {
		AllocationCard,
		AllocationLink,
	},
	data () {
		return {
			loaded: false,
			fetched: false,
			locked: false,
			shows: [],
			showData: [],
			selectedCategory: null,
			search: '',
			total: 'No',
		};
	},
	computed: {
		...mapState(['locks', 'categories', 'entries']),
		filteredCategories () {
			return this.categories.filter(category => category.entryType === 'shows' && this.entries.some(entry => entry.categoryId === category.id));
		},
		filteredEntries () {
			return this.entries.filter(entry => entry.categoryId === this.selectedCategory.id);
		},
		showIDs () {
			const entries = this.entries.filter(entry => entry.categoryId === this.selectedCategory.id);
			return entries.map(entry => entry.anilist_id);
		},
	},
	methods: {
		...mapActions(['getLocks', 'getCategories', 'getEntries']),
		changeCategory (category) {
			this.search = '';
			this.total = 'No';
			this.fetched = false;
			this.selectedCategory = category;
			this.fetchAnilist();
		},
		handleInput (event) {
			// TODO - this could just be a watcher
			this.search = event.target.value;
			this.fetched = false;
			clearTimeout(this.typingTimeout);
			this.typingTimeout = setTimeout(() => {
				this.sendQuery();
			}, 750);
		},
		sendQuery () {
			if (!this.search) {
				this.fetched = true;
				this.shows = this.showData;
				this.total = 'No';
				return;
			}
			const fuse = new Fuse(this.shows, options);
			this.shows = fuse.search(this.search);
			this.total = this.shows.length;
			this.fetched = true;
		},
		async fetchAnilist () {
			const promiseArray = [];
			let showData = [];
			let page = 1;
			const someData = await util.paginatedQuery(aq.showQuerySimple, this.showIDs, page);
			showData = [...showData, ...someData.data.Page.results];
			const lastPage = someData.data.Page.pageInfo.lastPage;
			page = 2;
			while (page <= lastPage) {
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
				this.showData = showData;
				this.shows = showData;
				this.fetched = true;
			});
		},
	},
	mounted () {
		Promise.all([this.getLocks(), this.getEntries(), this.getCategories()]).then(async () => {
			this.selectedCategory = this.filteredCategories[0];
			const allocLock = this.locks.find(lock => lock.name === 'allocations');
			if (!allocLock.flag) {
				this.locked = true;
				this.loaded = true;
				return;
			}
			await this.fetchAnilist();
			this.loaded = true;
		});
	},
};
</script>

<style lang="scss">
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
</style>
