/* eslint-disable no-await-in-loop */
<template>
    <div class="section">
        <h2 class="title">Results</h2>
        <div v-if="!loaded || !dashTotals || !opedTotals" class="content">
            <p>Loading...</p>
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
									<li v-for="(votes, index) in votesFor(category)" :key="index" class="mb-1 has-no-bullet">
										{{votes.name}}
										<br>
										<div class="tags">
											<small class="tag is-small">{{votes.vote_count}} votes</small>
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
import * as aq from '../../voting/anilistQueries';

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
		};
	},
	computed: {
		...mapState([
			'categories',
			'voteTotals',
			'dashTotals',
			'opedTotals',
			'themes',
			'voteSummary',
		]),
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
			'getDashboardTotals',
			'getOPEDTotals',
			'getThemes',
			'getVoteSummary',
		]),
		isDashboard (category) {
			if (category.awardsGroup === 'genre') {
				return true;
			} else if (category.awardsGroup === 'main' && category.name !== 'Anime of the Year') {
				return true;
			} else if (category.awardsGroup === 'test' && category.name.includes('Sports')) {
				return true;
			} else if (category.name.includes('OST') && category.awardsGroup === 'production') {
				return true;
			}
			return false;
		},
		votesFor (category) {
			let allVotes = [];
			//console.log(category.name);
			if (this.isDashboard(category)) {
				allVotes = this.dashTotals.filter(vote => vote.category_id === category.id);
				//console.log('dashboard');
			} else if (category.entryType === 'themes') {
				allVotes = this.opedTotals.filter(vote => vote.category_id === category.id);
				//console.log('op/ed');
			} else {
				allVotes = this.voteTotals.filter(vote => vote.category_id === category.id);
				//console.log('other');
			}
			const entries = [];
			for (const vote of allVotes) {
				if (this.isDashboard(category)) { // This condition is fulfilled for dashboard cats only
					// Dashboard categories have their anilist stored here
					const requiredShow = this.showData.find(show => show.id === vote.anilist_id);
					if (requiredShow) {
						entries.push({
							vote_count: vote.vote_count,
							name: `${requiredShow.title.romaji}`,
							image: `${requiredShow.coverImage.large}`,
						});
					}
				} else if (category.entryType === 'themes') { // redundant condition for theme cats
					const requiredShow = this.showData.find(show => show.id === vote.anilist_id);
					const requiredTheme = this.themes.find(theme => theme.id === vote.entry_id);
					if (requiredShow && requiredTheme) {
						entries.push({
							vote_count: vote.vote_count,
							name: `${requiredShow.title.romaji} - ${requiredTheme.title} ${requiredTheme.themeNo}`,
							image: `${requiredShow.coverImage.large}`,
						});
					}
				} else if (category.entryType === 'shows') { // If it's an anilist show cat
					const requiredShow = this.showData.find(show => show.id === vote.entry_id);
					if (requiredShow) {
						entries.push({
							vote_count: vote.vote_count,
							name: `${requiredShow.title.romaji}`,
							image: `${requiredShow.coverImage.large}`,
						});
					}
				} else if (category.entryType === 'characters') {
					// if ([101338, 104071, 104212, 105928, 107663, 111131].includes(vote.entry_id)) continue;
					const requiredChar = this.charData.find(char => char.id === vote.entry_id);
					console.log(requiredChar);
					console.log(vote.entry_id);
					entries.push({
						vote_count: vote.vote_count,
						name: `${requiredChar.name.full}`,
						image: `${requiredChar.image.large}`,
					});
				} else if (category.entryType === 'vas') {
					// if ([101338, 104071, 104212, 105928, 107663, 111131].includes(vote.entry_id)) continue;
					const requiredChar = this.charData.find(char => char.id === vote.entry_id);
					console.log(requiredChar);
					console.log(vote.entry_id);
					entries.push({
						vote_count: vote.vote_count,
						name: `${requiredChar.name.full} (${requiredChar.media.edges[0].voiceActors[0].name.full})`,
						image: `${requiredChar.image.large}`,
					});
				}
			}
			//console.log(allVotes);
			// console.log(entries);
			return entries;
		},
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
					if (!charaResponse.ok) return alert('no bueno');
					const returnData = await charaResponse.json();
					this.charData = [...this.charData, ...returnData.data.Page.results];
					resolve(returnData);
				} catch (err) {
					reject(err);
				}
			});
		},
		sendQueries () {
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
			Promise.all([catPromise, votesPromise]).then(() => {
				for (const vote of this.voteTotals) {
					const category = this.categories.find(cat => cat.id === vote.category_id);
					if (this.isDashboard(category)) { // This condition is fulfilled for dashboard cats only
					// Dashboard categories have their anilist stored here
						this.showIDs.push(vote.anilist_id);
					} else if (category.entryType === 'themes') { // redundant condition for theme cats
						this.showIDs.push(vote.anilist_id);
					} else if (category.entryType === 'shows') { // If it's an anilist show cat
						this.showIDs.push(vote.entry_id);
					} else if (category.entryType === 'characters' || category.entryType === 'vas') {
						this.charIDs.push(vote.entry_id);
					}
				}
				this.showIDs = [...new Set(this.showIDs)];
				this.charIDs = [...new Set(this.charIDs)];
				const showPromise = new Promise(async (resolve, reject) => {
					try {
						let lastPage = false;
						let page = 1;
						while (!lastPage) {
							const returnData = await this.fetchShows(page);
							lastPage = returnData.data.Page.pageInfo.currentPage === returnData.data.Page.pageInfo.lastPage;
							page++;
						}
						resolve();
					} catch (err) {
						reject(err);
					}
				});
				const charPromise = new Promise(async (resolve, reject) => {
					try {
						let lastPage = false;
						let page = 1;
						while (!lastPage) {
							const returnData = await this.fetchChars(page);
							lastPage = returnData.data.Page.pageInfo.currentPage === returnData.data.Page.pageInfo.lastPage;
							page++;
						}
						resolve();
					} catch (err) {
						reject(err);
					}
				});
				Promise.all([showPromise, charPromise]).then(() => {
					this.loaded = true;
				});
			});
		},
	},
	mounted () {
		this.sendQueries();
		if (!this.themes) {
			this.getThemes();
		}
		if (!this.dashTotals) {
			this.getDashboardTotals();
		}
		if (!this.opedTotals) {
			this.getOPEDTotals();
		}
		if (!this.voteSummary) {
			this.getVoteSummary();
		}
	},
};
</script>
