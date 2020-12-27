/* eslint-disable no-unused-vars */
/* eslint-disable no-await-in-loop */
import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const util = require('../util');
const queries = require('../anilistQueries');

const constants = require('../../constants');

async function makeRequest (path, method = 'GET', body) {
	if (typeof body === 'object' && body != null) {
		body = JSON.stringify(body);
	}
	try {
		const result = await fetch(path, {method, body});
		if (!result.ok) {
			const json = await result.json();
			throw json.error;
		}
		if (result.status === 204) {
			return;
		}
		return await result.json();
	} catch (error) {
		// eslint-disable-next-line no-alert
		window.alert(error);
		throw error;
	}
}

const store = new Vuex.Store({
	state: {
		me: null,
		categories: null,
		themes: null,
		votingCats: null,
		selections: null,
		entries: null,
		locks: null,
	},
	getters: {
		accountOldEnough (state) {
			return state.me && state.me.reddit && state.me.reddit.created < constants.maxAccountDate;
		},
	},
	mutations: {
		GET_ME (state, me) {
			state.me = me;
		},
		GET_CATEGORIES (state, categories) {
			state.categories = categories;
		},
		GET_VOTING_CATEGORIES (state, votingCats) {
			state.votingCats = votingCats;
		},
		UPDATE_THEMES (state, themes) {
			state.themes = themes;
		},
		UPDATE_SELECTIONS (state, selections) {
			state.selections = selections;
		},
		GET_ENTRIES (state, entries) {
			state.entries = entries;
		},
		GET_LOCKS (state, locks) {
			state.locks = locks;
		},
	},
	actions: {
		async getMe ({commit}) {
			const response = await fetch('/api/user/me');
			if (!response.ok) return;
			const me = await response.json();
			commit('GET_ME', me);
		},
		async getCategories ({commit}) {
			const categories = await makeRequest('/api/category/all');
			commit('GET_CATEGORIES', categories);
		},
		async getThemes ({commit}) {
			const themes = await makeRequest('/api/themes');
			commit('UPDATE_THEMES', themes);
		},
		async getVotingCategories ({commit, state, dispatch}, group) {
			if (!state.categories) {
				await dispatch('getCategories');
			}
			const votingCats = state.categories.filter(cat => cat.awardsGroup === group);
			commit('GET_VOTING_CATEGORIES', votingCats);
		},
		async getLocks ({commit}) {
			const locks = await makeRequest('/api/locks/all');
			commit('GET_LOCKS', locks);
		},
		async initializeSelections ({commit, state, dispatch}) {
			if (!state.categories) {
				await dispatch('getCategories');
			}
			const selections = {};
			const allIDs = {
				shows: [],
				chars: [],
				vas: [],
			};
			// this is gonna hold all themes data from data and after squashing, push it into selections
			const themeObject = {
				op: [],
				ed: [],
			};
			for (const cat of state.categories) {
				selections[cat.id] = [];
			}
			const votes = await makeRequest('/api/votes/get');
			// Check if user has voted
			if (votes.length !== 0) {
				// Big fucking messy code that I will surely end myself after writing
				// Haha yes, this just had to become EVEN FUCKING SLOWER, we basically need theme info to load votes
				if (!state.themes) {
					await dispatch('getThemes');
				}
				for (const vote of votes) {
					const category = state.categories.find(cat => cat.id === vote.category_id); // retrieve category associated with the vote
					if (category.entryType === 'shows') {
						// All of these are pushing anilist IDs into a bunch of arrays for querying
						allIDs.shows.push(vote.entry_id);
					} else if (vote.theme_name) {
						// Theme category so we're gonna push the whole theme and SQUASH this shit later
						const theme = state.themes.find(themeData => themeData.id === vote.entry_id);
						themeObject[theme.themeType].push(theme);
					} else if (category.entryType === 'characters') {
						// All of these are pushing anilist IDs into a bunch of arrays for querying
						allIDs.chars.push(vote.entry_id);
					} else if (category.entryType === 'vas') {
						allIDs.vas.push(vote.entry_id);
					}
				}
				// Check if user actually voted for any OPs/EDs
				if (themeObject.op.length !== 0 || themeObject.ed.length !== 0) {
					// Pull anilist IDs of themes
					let themeArr = [];
					if (themeObject.op.length !== 0) {
						themeArr = themeArr.concat(themeObject.op.map(theme => theme.anilistID));
					}
					if (themeObject.ed.length !== 0) {
						themeArr = themeArr.concat(themeObject.ed.map(theme => theme.anilistID));
					}
					const opCat = state.categories.find(cat => cat.name.includes('OP')); // hard coded shit, need these categories so info goes back into them proper
					const edCat = state.categories.find(cat => cat.name.includes('ED'));
					const anilistData = await util.makeQuery(queries.themeByIDQuery, themeArr); // await ahhhhhh
					for (const theme of themeObject.op) {
						const foundTheme = anilistData.find(show => show.id === theme.anilistID); // search for show info associated with theme
						selections[opCat.id].push({...foundTheme, ...theme}); // SQUASH them together and push into selections
					}
					for (const theme of themeObject.ed) { // repeat
						const foundTheme = anilistData.find(show => show.id === theme.anilistID);
						selections[edCat.id].push({...foundTheme, ...theme});
					}
				}
				// The final yeet
				const showsData = allIDs.shows.length === 0 ? [] : await util.makeQuery(queries.showByIDQuery, allIDs.shows); // need to make these execute asynchronously somehow
				const charData = allIDs.chars.length === 0 ? [] : await util.makeQuery(queries.charByIDQuery, allIDs.chars);
				const vaData = allIDs.vas.length === 0 ? [] : await util.makeQuery(queries.vaByIDQuery, allIDs.vas);
				for (const vote of votes) {
					const category = state.categories.find(cat => cat.id === vote.category_id); // find category associated with vote
					if (!vote.theme_name) { // This condition skips the loop if it's a theme cat
						if (vote.anilist_id && !vote.theme_name) { // if dashboard cat, compare anilist_id instead
							selections[category.id].push(showsData.find(show => show.id === vote.anilist_id));
						} else if (category.entryType === 'shows') {
							selections[category.id].push(showsData.find(show => show.id === vote.entry_id)); // just push stuff into objects
						} else if (category.entryType === 'characters') {
							selections[category.id].push(charData.find(char => char.id === vote.entry_id));
						} else if (category.entryType === 'vas') {
							selections[category.id].push(vaData.find(va => va.id === vote.entry_id));
						}
					}
				}
			}
			commit('UPDATE_SELECTIONS', selections);
		},
		async getEntries ({commit}) {
			const entries = await makeRequest('/api/category/entries/vote');
			commit('GET_ENTRIES', entries);
		},
	},
});

export default store;
