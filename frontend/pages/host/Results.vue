<template>
    <div class="section">
        <h2 class="title">Results</h2>
        <div v-if="!voteTotals || !categories || !loaded" class="content">
            <p>Loading...</p>
        </div>
        <div v-else class="content">
            <h3>Overall</h3>
            <div
                v-for="category in categories"
                :key="category.id"
            >
                <h3>{{category.name}}</h3>
				<table class="table is-hoverable is-fullwidth">
					<tbody>
					<tr>
						<th>Rank</th>
						<th>Entry Name</th>
						<th>Vote Count</th>
					</tr>
					<tr v-for="(votes, index) in votesFor(category)" :key="index">
						<td>#{{index+1}}</td>
						<td>{{votes.name}}</td>
						<td>{{votes.vote_count}}</td>
					</tr>
					</tbody>
				</table>
            </div>
        </div>
    </div>
</template>

<script>
import {mapActions, mapState} from 'vuex';

const showQuery = `query ($id: [Int]) {
	Page {
	  pageInfo {
		total
	  }
	  results: media(type: ANIME, id_in: $id) {
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
  }`;
const charQuery = `query ($id: [Int]) {
	Page {
	  pageInfo {
		total
	  }
	  results: characters(id_in: $id) {
		id
		name {
		  full
		}
		image {
		  large
		}
		media(sort: [END_DATE_DESC, START_DATE_DESC], type: ANIME, page: 1, perPage: 1) {
		  nodes {
			id
			title {
			  romaji
			  english
			  native
			  userPreferred
			}
			endDate {
			  year
			  month
			  day
			}
		  }
		  edges {
			id
			node {
			  id
			}
			characterRole
			voiceActors(language: JAPANESE) {
			  id
			  name {
				full
				alternative
				native
			  }
			}
		  }
		}
		siteUrl
	  }
	}
  }`;

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
		]),
	},
	methods: {
		...mapActions([
			'getCategories',
			'getVoteTotals',
			'getThemes',
		]),
		votesFor (category) {
			const allVotes = this.voteTotals.filter(vote => vote.category_id === category.id);
			const entries = [];
			for (const vote of allVotes) {
				if (vote.anilist_id && !vote.theme_name) { // This condition is fulfilled for dashboard cats only
					// Dashboard categories have their anilist stored here
					const requiredShow = this.showData.find(show => show.id === vote.anilist_id);
					entries.push({
						vote_count: vote.vote_count,
						name: `${requiredShow.title.romaji}`,
					});
				} else if (vote.theme_name) { // redundant condition for theme cats
					const requiredShow = this.showData.find(show => show.id === vote.anilist_id);
					const requiredTheme = this.themes.find(theme => theme.id === vote.entry_id);
					entries.push({
						vote_count: vote.vote_count,
						name: `${requiredShow.title.romaji} - ${requiredTheme.title} ${requiredTheme.themeNo}`,
					});
				} else if (category.entryType === 'shows' || category.name.includes('Cast')) { // If it's an anilist show cat
					const requiredShow = this.showData.find(show => show.id === vote.entry_id);
					entries.push({
						vote_count: vote.vote_count,
						name: `${requiredShow.title.romaji}`,
					});
				} else if (category.entryType === 'characters') {
					const requiredChar = this.charData.find(char => char.id === vote.entry_id);
					console.log(requiredChar);
					entries.push({
						vote_count: vote.vote_count,
						name: `${requiredChar.name.full}`,
					});
				} else if (category.entryType === 'vas') {
					const requiredChar = this.charData.find(char => char.id === vote.entry_id);
					console.log(requiredChar);
					entries.push({
						vote_count: vote.vote_count,
						name: `${requiredChar.name.full} (${requiredChar.media.edges[0].voiceActors[0].name.full})`,
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
					},
				}),
			});
			if (!showResponse.ok) return alert('no bueno');
			this.showData = (await showResponse.json()).data.Page.results;
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
					},
				}),
			});
			if (!charaResponse.ok) return alert('no bueno');
			this.charData = (await charaResponse.json()).data.Page.results;
			console.log(this.showData);
			console.log(this.charData);
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
	},
};
</script>
