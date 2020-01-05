<template>
    <div class="section">
        <h2 class="title">Results</h2>
        <div v-if="!voteTotals || !categories || !loaded" class="content">
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
										<small class="tag is-small">{{votes.vote_count}} votes</small>
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
			showData: null,
			charData: null,
			loaded: false,
		};
	},
	computed: {
		...mapState([
			'categories',
			'voteTotals',
			'themes',
			'voteSummary',
		]),
		groupedThemeVotes: function(){
			const themeVotes = this.voteTotals.filter(vote => (vote.theme_name));
			const themeVotesGrouped = new Array();
			for (const vote of themeVotes){
				if (!vote) continue;
				const gvoteIndex = themeVotesGrouped.findIndex(gvote => (gvote.theme_name == vote.theme_name));
				
				if (gvoteIndex > -1){
					if (themeVotesGrouped[gvoteIndex].vote_count < vote.vote_count){
						themeVotesGrouped[gvoteIndex].entry_id = vote.entry_id;
					}
					themeVotesGrouped[gvoteIndex].vote_count += vote.vote_count;
				} else {
					themeVotesGrouped.push(vote);
				}
			}
			//console.log(themeVotesGrouped);
			themeVotesGrouped.sort((a,b) => (b.vote_count - a.vote_count));

			return themeVotesGrouped;
		},
		groupedDashboardVotes: function(){
			const dashboardVotes = this.voteTotals.filter(vote => (vote.anilist_id && !vote.theme_name));
			const dashboardVotesGrouped = new Array();
			for (const vote of dashboardVotes){
				if (!vote) continue;
				const gvoteIndex = dashboardVotesGrouped.findIndex(gvote => (gvote.anilist_id == vote.anilist_id));
				if (gvoteIndex > -1){
					dashboardVotesGrouped[gvoteIndex].vote_count += vote.vote_count;
				} else {
					dashboardVotesGrouped.push(vote);
				}
			}
			//console.log(dashboardVotesGrouped);
			dashboardVotesGrouped.sort((a,b) => (b.vote_count - a.vote_count));
			return dashboardVotesGrouped;
		}
	},
	methods: {
		...mapActions([
			'getCategories',
			'getVoteTotals',
			'getThemes',
			'getVoteSummary',
		]),
		votesFor (category) {
			var allVotes = new Array();
			if (category.entries && category.entries != '[]'){
				allVotes = this.groupedDashboardVotes.filter(vote => vote.category_id === category.id);
				//console.log('dashboard');
			} else if (category.entryType == 'themes'){
				allVotes = this.groupedThemeVotes.filter(vote => vote.category_id === category.id);
			}
			else{
				allVotes = this.voteTotals.filter(vote => vote.category_id === category.id);
			}
			const entries = [];
			for (const vote of allVotes) {
				if (vote.anilist_id && !vote.theme_name) { // This condition is fulfilled for dashboard cats only
					// Dashboard categories have their anilist stored here
					const requiredShow = this.showData.find(show => show.id === vote.anilist_id);
					if (requiredShow){
						entries.push({
							vote_count: vote.vote_count,
							name: `${requiredShow.title.romaji}`,
							image: `${requiredShow.coverImage.large}`,
						});
					}
				} else if (vote.theme_name) { // redundant condition for theme cats
					const requiredShow = this.showData.find(show => show.id === vote.anilist_id);
					const requiredTheme = this.themes.find(theme => theme.id === vote.entry_id);
					if (requiredShow && requiredTheme){
						entries.push({
							vote_count: vote.vote_count,
							name: `${requiredShow.title.romaji} - ${requiredTheme.title} ${requiredTheme.themeNo}`,
							image: `${requiredShow.coverImage.large}`,
						});
					}
				} else if (category.entryType === 'shows' || category.name.includes('Cast')) { // If it's an anilist show cat
					const requiredShow = this.showData.find(show => show.id === vote.entry_id);
					if (requiredShow){
						entries.push({
							vote_count: vote.vote_count,
							name: `${requiredShow.title.romaji}`,
							image: `${requiredShow.coverImage.large}`,
						});
					}
				} else if (category.entryType === 'characters') {
					const requiredChar = this.charData.find(char => char.id === vote.entry_id);
					//console.log(requiredChar);
					entries.push({
						vote_count: vote.vote_count,
						name: `${requiredChar.name.full}`,
						image: `${requiredChar.image.large}`,
					});
				} else if (category.entryType === 'vas') {
					const requiredChar = this.charData.find(char => char.id === vote.entry_id);
					//console.log(requiredChar);
					entries.push({
						vote_count: vote.vote_count,
						name: `${requiredChar.name.full} (${requiredChar.media.edges[0].voiceActors[0].name.full})`,
						image: `${requiredChar.image.large}`,
					});
				}
			}
			return entries;
		},
		async sendQueries () {
			const showIDs = [];
			const charIDs = [];
			for (const vote of this.voteTotals) {
				const category = this.categories.find(cat => cat.id === vote.category_id);
				if (vote.anilist_id && !vote.theme_name) { // This condition is fulfilled for dashboard cats only
					// Dashboard categories have their anilist stored here
					showIDs.push(vote.anilist_id);
				} else if (vote.theme_name) { // redundant condition for theme cats
					showIDs.push(vote.anilist_id);
				} else if (category.entryType === 'shows') { // If it's an anilist show cat
					showIDs.push(vote.entry_id);
				} else if (category.entryType === 'characters' || category.entryType === 'vas') {
					charIDs.push(vote.entry_id);
				}
			}

			var lastPage = false;
			var page = 1;
			this.showData = new Array();

			while (!lastPage){
				const showResponse = await fetch('https://graphql.anilist.co', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
					},
					body: JSON.stringify({
						query: showQuery,
						variables: {
							id: showIDs,
							page: page,
							perPage: 50,
						},
					}),
				});
				if (!showResponse.ok) return alert('no bueno');
				const returnData = await showResponse.json();
				this.showData = this.showData.concat(returnData.data.Page.results);

				lastPage = (returnData.data.Page.pageInfo.currentPage == returnData.data.Page.pageInfo.lastPage);
				//console.log(returnData);
				page++;
			}

			//console.log(this.showData);
			
			lastPage = false;
			page = 1;
			this.charData = new Array();
			while (!lastPage){
				const charaResponse = await fetch('https://graphql.anilist.co', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
					},
					body: JSON.stringify({
						query: charQuery,
						variables: {
							id: charIDs,
							page: page,
							perPage: 50,
						},
					}),
				});
				if (!charaResponse.ok) return alert('no bueno');
				const returnData = await charaResponse.json();
				this.charData = this.charData.concat(returnData.data.Page.results);

				lastPage = (returnData.data.Page.pageInfo.currentPage == returnData.data.Page.pageInfo.lastPage);
				//console.log(returnData);
				page++;
			}
			//console.log(this.showData);
			//console.log(this.charData);
			//console.log(this.voteTotals);
			//console.log(this.categories);
			this.loaded = true;
		},
	},
	async mounted () {
		if (!this.categories) {
			await this.getCategories();
		}
		if (!this.voteTotals) {
			await this.getVoteTotals();
		}
		if (!this.themes) {
			await this.getThemes();
		}
		this.sendQueries();
		if (!this.voteSummary) {
			this.getVoteSummary();
		}

		console.log(this.groupedThemeVotes);
	},
};
</script>
