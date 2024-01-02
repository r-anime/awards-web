/* eslint-disable no-await-in-loop */
<template>
<div v-if="locked !== null">
    <div class="section" v-if="locked === false">
        <h2 class="title">Results</h2>
        <div v-if="!loaded" class="content">
            <h3>{{loadingprogress.curr}}/{{loadingprogress.max}}</h3>
			<progress class="progress is-primary" :value="loadingprogress.curr" :max="loadingprogress.max">{{loadingprogress.curr}}/{{loadingprogress.max}}</progress>
        </div>
        <div v-else class="content">
            <div class="field is-grouped is-grouped-multiline">
				<div class="control">
					<div class="tags has-addons are-medium">
						<span class="tag is-dark">Total Votes</span>
						<span class="tag is-primary">{{voteSummary.votes}}</span>
					</div>
				</div>
				<div class="control">
					<div class="tags has-addons are-medium">
						<span class="tag is-dark">Total Users</span>
						<span class="tag is-primary">{{voteSummary.users}}</span>
					</div>
				</div>
			</div>
			<br>
			<div class="columns is-multiline">
				<div
					v-for="category in categories"
					:key="category.id"

					class="column is-6 is-4-desktop is-3-widescreen"
				>
					<div class="card">
						<header class="card-header has-background-light">
							<div class="card-header-title">
								<p class="title is-4">{{category.name}}</p>
							</div>
						</header>
						<div class="card-content is-fixed-height-scrollable-300">
							<div class="content">
								<ul>
									<li v-for="(vote, index) in votes[category.id]" :key="index" class="mb-1 has-no-bullet">
										{{vote.name}}
										<br>
										<div class="tags">
											<small class="tag is-small">{{vote.vote_count}} votes</small>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
        </div>
    </div>
	<div v-else-if="locked" class="section">
		You are not allowed to view the results at this time.
	</div>
</div>
</template>

<style>
.is-fixed-height-scrollable-300 {
	height: 300px;
	overflow: auto;
}
.has-no-bullet{
	list-style: none;
}
.mb-1{
	margin-bottom: 0.5rem;
}
</style>

<script>
import {mapActions, mapState} from 'vuex';
import * as aq from '../../anilistQueries';

const showQuery = aq.showQuerySimple;
const charQuery = aq.charQuerySimple;

export default {
	data () {
		return {
			showData: [],
			charData: [],
			showIDs: [],
			charIDs: [],
			loaded: false,
			locked: null,
			votes: {},
		};
	},
	computed: {
		...mapState([
			'categories',
			'voteTotals',
			'opedTotals',
			'themes',
			'voteSummary',
			'locks',
			'me',
			'items',
			'loadingprogress',
		]),
		// eslint-disable-next-line multiline-comment-style
		/*
		groupedThemeVotes () {
			const themeVotes = this.voteTotals.filter(vote => vote.theme_name);
			const themeVotesGrouped = new Array();
			for (const vote of themeVotes) {
				if (!vote) continue;
				const gvoteIndex = themeVotesGrouped.findIndex(gvote => gvote.theme_name == vote.theme_name);

				if (gvoteIndex > -1) {
					if (themeVotesGrouped[gvoteIndex].vote_count < vote.vote_count) {
						themeVotesGrouped[gvoteIndex].entry_id = vote.entry_id;
					}
					themeVotesGrouped[gvoteIndex].vote_count += vote.vote_count;
				} else {
					themeVotesGrouped.push(vote);
				}
			}
			// console.log(themeVotesGrouped);
			themeVotesGrouped.sort((a, b) => b.vote_count - a.vote_count);
			this.gtvloaded = true;
			return themeVotesGrouped;
		},
		groupedDashboardVotes () {
			const dashboardVotes = this.voteTotals.filter(vote => vote.anilist_id && !vote.theme_name);
			const dashboardVotesGrouped = new Array();
			for (const vote of dashboardVotes) {
				if (!vote) continue;
				const gvoteIndex = dashboardVotesGrouped.findIndex(gvote => (gvote.anilist_id == vote.anilist_id && gvote.category_id == vote.category_id));
				if (gvoteIndex >= 0) {
					dashboardVotesGrouped[gvoteIndex].vote_count += vote.vote_count;
					//console.log(dashboardVotesGrouped[gvoteIndex].vote_count, vote.anilist_id);
				} else {
					dashboardVotesGrouped.push(vote);
				}
			}
			// console.log(dashboardVotesGrouped);
			dashboardVotesGrouped.sort((a, b) => b.vote_count - a.vote_count);
			this.gdvloaded = true;
			return dashboardVotesGrouped;
		},
		*/
	},
	methods: {
		...mapActions([
			'getCategories',
			'getVoteTotals',
			'getOPEDTotals',
			'getThemes',
			'getVoteSummary',
			'getLocks',
			'getMe',
			'getItems',
		]),
		fetchShows (page) {
			return new Promise(async (resolve, reject) => {
				try {
					const showResponse = await fetch('https://graphql.anilist.co', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'Accept': 'application/json',
						},
						body: JSON.stringify({
							query: showQuery,
							variables: {
								id: this.showIDs,
								page,
								perPage: 50,
							},
						}),
					});
					// eslint-disable-next-line no-alert
					if (!showResponse.ok) return alert('no bueno');
					const returnData = await showResponse.json();
					this.showData = [...this.showData, ...returnData.data.Page.results];
					resolve(returnData);
				} catch (err) {
					reject(err);
				}
			});
		},
		fetchChars (page) {
			return new Promise(async (resolve, reject) => {
				try {
					const charaResponse = await fetch('https://graphql.anilist.co', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'Accept': 'application/json',
						},
						body: JSON.stringify({
							query: charQuery,
							variables: {
								id: this.charIDs,
								page,
								perPage: 50,
							},
						}),
					});
					// eslint-disable-next-line no-alert
					if (!charaResponse.ok){
						reject(charaResponse);
					}
					const returnData = await charaResponse.json();
					this.charData = [...this.charData, ...returnData.data.Page.results];
					resolve(returnData);
				} catch (err) {
					reject(err);
				}
			});
		},
	},
	mounted () {
		Promise.all([this.getLocks(), this.getMe()]).then(() => {
			const lock = this.locks.find(aLock => aLock.name === 'hostResults');
			if (lock.flag || this.me.level > lock.level) this.locked = false;
			else this.locked = true;
			if (this.locked) {
				this.loaded = true;
			} else {
				const themePromise = new Promise(async (resolve, reject) => {
					try {
						if (!this.themes) {
							await this.getThemes();
						}
						resolve();
					} catch (error) {
						reject(error);
					}
				});
				const catPromise = new Promise(async (resolve, reject) => {
					try {
						if (!this.categories) {
							await this.getCategories();
						}
						resolve();
					} catch (err) {
						reject(err);
					}
				});
				const votesPromise = new Promise(async (resolve, reject) => {
					try {
						if (!this.voteTotals) {
							await this.getVoteTotals();
						}
						resolve();
					} catch (err) {
						reject(err);
					}
				});
				const itemsPromise = new Promise(async (resolve, reject) => {
					try {
						if (!this.items || this.items.length == 0) {
							await this.getItems();
						}
						resolve();
					} catch (err) {
						reject(err);
					}
				});
				Promise.all([themePromise, catPromise, votesPromise, itemsPromise]).then(() => {
					const opedPromise = new Promise(async (resolve, reject) => {
						try {
							if (!this.opedTotals) {
								await this.getOPEDTotals();
							}
							resolve();
						} catch (error) {
							reject(error);
						}
					});
					const summaryPromise = new Promise(async (resolve, reject) => {
						try {
							if (!this.voteSummary) {
								await this.getVoteSummary();
							}
							resolve();
						} catch (error) {
							reject(error);
						}
					});
					for (const vote of this.voteTotals) {
						const category = this.categories.find(cat => cat.id === vote.category_id);
						if (category.entryType === 'themes') {
							this.showIDs.push(vote.anilist_id);
						}
					}
					this.showIDs = [...new Set(this.showIDs)];
					const showPromise = new Promise(async (resolve, reject) => {
						try {
							const lastPage = Math.ceil(this.charIDs.length/50);
							let page = 1;
							while (page <= lastPage) {
							// eslint-disable-next-line no-await-in-loop
								const returnData = await this.fetchShows(page);
								lastPage = returnData.data.Page.pageInfo.currentPage === returnData.data.Page.pageInfo.lastPage;
								page++;
							}
							resolve();
						} catch (err) {
							reject(err);
						}
					});
					Promise.all([opedPromise, summaryPromise, showPromise]).then(() => {
						for (const category of this.categories) {
							let allVotes = [];
							if (category.entryType === 'themes') {
								allVotes = this.opedTotals.filter(vote => vote.category_id === category.id);
							} else {
								allVotes = this.voteTotals.filter(vote => vote.category_id === category.id);
							}
							const entries = [];
							console.log(this.showData);
							for (const vote of allVotes) {
								if (category.entryType === 'themes') {
									const requiredTheme = this.themes.find(theme => theme.id == vote.entry_id);
									// const requiredShow = this.items.find(show => show.anilistID == requiredTheme.anilistID);
									
									if (requiredTheme) {
										entries.push({
											vote_count: vote.vote_count,
											name: `${requiredTheme.anime} - ${requiredTheme.theme_name} ${requiredTheme.themeNo}`,
										});
									}
								} else if (category.entryType === 'shows') {
									const requiredShow = this.items.find(show => show.id === vote.entry_id);
									if (requiredShow) {
										const name = requiredShow.english || requiredShow.romanji;
										entries.push({
											vote_count: vote.vote_count,
											name: `${name}`,
										});
									} else {
										entries.push({
											vote_count: vote.vote_count,
											name: `${vote.entry_id}`,
										});
									}
								} else if (category.entryType === 'characters') {
									const requiredChar = this.items.find(char => char.id === vote.entry_id);
									if (requiredChar) {
										const name = requiredChar.english || requiredChar.romanji;
										let show = "";
										if (requiredChar){
											show = requiredChar["parent.english"] || requiredChar["parent.romanji"];
										}
										entries.push({
											vote_count: vote.vote_count,
											name: `${name} (${show})`,
										});
									} else {
										entries.push({
											vote_count: vote.vote_count,
											name: `${vote.entry_id}`,
										});
									}
								} else if (category.entryType === 'vas') {
									const requiredChar = this.items.find(char => char.id === vote.entry_id);
									if (requiredChar) {
										const name = requiredChar.english || requiredChar.romanji;
										let char = "";
										if (requiredChar){
											char = requiredChar["parent.english"] || requiredChar["parent.romanji"];
										}
										entries.push({
											vote_count: vote.vote_count,
											name: `${name} (${char})`,
										});
									} else {
										entries.push({
											vote_count: vote.vote_count,
											name: `${vote.entry_id}`,
										});
									}
								}
							}
							this.votes[category.id] = entries;
						}
						this.loaded = true;
					});
				});
			}
		});
	},
};
</script>
