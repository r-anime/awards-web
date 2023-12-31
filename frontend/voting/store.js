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
		console.log(error);
		return {};
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
		items: [],
		locks: null,
		loadingprogress: {
			curr: 0,
			max: 0,
		}
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
		SET_ITEMS (state, items) {
			state.items = items;
		},
		SET_LOADING (state, data) {
			state.loadingprogress.curr = data.curr;
			state.loadingprogress.max = data.max;
		}
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
			};
			// this is gonna hold all themes data from data and after squashing, push it into selections
			const themeObject = {
				op: [],
				ed: [],
			};
			for (const cat of state.categories) {
				selections[cat.id] = [];
			}
			if (!state.locks) {
				await dispatch('getLocks');
			}
			if (!state.me) {
				await dispatch('getMe');
			}
			const voteLock = state.locks.find(aLock => aLock.name === 'voting');
			if (!voteLock.flag && state.me.level < voteLock.level) {
				return commit('UPDATE_SELECTIONS', selections);
			}
			let votes = await makeRequest('/api/votes/get');
			// console.log(votes);
			// Check if user has voted
			if (!votes || (Object.keys(votes).length === 0 && votes.constructor === Object)) {
				commit('UPDATE_SELECTIONS', selections);
			} else {
				// Big fucking messy code that I will surely end myself after writing
				// Haha yes, this just had to become EVEN FUCKING SLOWER, we basically need theme info to load votes
				if (!state.themes) {
					await dispatch('getThemes');
				}
				for (const vote of votes) {
					const category = state.categories.find(cat => cat.id === vote.category_id);
					if (category){ // retrieve category associated with the vote
						if (category.entryType === 'shows') {
							// All of these are pushing anilist IDs into a bunch of arrays for querying
							allIDs.shows.push(vote.entry_id);
						} else if (vote.theme_name) {
							// Theme category so we're gonna push the whole theme and SQUASH this shit later
							const theme = state.themes.find(themeData => themeData.id === vote.entry_id);
							if (theme){
								themeObject[theme.themeType].push(theme);
							}
						} else if (category.entryType === 'characters' || category.entryType === 'vas') {
							// All of these are pushing anilist IDs into a bunch of arrays for querying
							allIDs.chars.push(vote.entry_id);
						}
					}
				}
				// Check if user actually voted for any OPs/EDs
				if (themeObject.op.length !== 0 || themeObject.ed.length !== 0) {
					// Pull anilist IDs of themes
					if (themeObject.op.length !== 0) {
						allIDs.shows = allIDs.shows.concat(themeObject.op.map(theme => theme.anilistID));
					}
					if (themeObject.ed.length !== 0) {
						allIDs.shows = allIDs.shows.concat(themeObject.ed.map(theme => theme.anilistID));
					}
				}
				
				if (!state.items || state.items.length === 0) {
					await dispatch('getItems');
				}

				const opCat = state.categories.find(cat => cat.name.includes('OP')); // hard coded shit, need these categories so info goes back into them proper
				const edCat = state.categories.find(cat => cat.name.includes('ED'));
				for (const theme of themeObject.op) {
					const foundTheme = state.items.find(show => show.anilistID === theme.anilistID); // search for show info associated with theme
					selections[opCat.id].push({...foundTheme, ...theme}); // SQUASH them together and push into selections
				}
				for (const theme of themeObject.ed) { // repeat
					const foundTheme = state.items.find(show => show.anilistID === theme.anilistID);
					selections[edCat.id].push({...foundTheme, ...theme});
				}
				for (const vote of votes) {
					const category = state.categories.find(cat => cat.id === vote.category_id); // find category associated with vote
					if (category){
						if (!vote.theme_name) { // This condition skips the loop if it's a theme cat
							if (category.entryType === 'shows') {
								selections[category.id].push(state.items.find(show => show.id === vote.entry_id)); // just push stuff into objects
							} else if (category.entryType === 'characters' || category.entryType === 'vas') {
								selections[category.id].push(state.items.find(char => char.id === vote.entry_id));
							}
						}
					}
				}
				commit('UPDATE_SELECTIONS', selections);
			}
		},
		async getEntries ({commit}) {
			const entries = await makeRequest('/api/category/entries/vote');
			commit('GET_ENTRIES', entries);
		},
		async getItems ({commit}){
			const req = await makeRequest(`/api/items/page/0`, 'GET');
			let page = 0;
			const items = [...req.rows];
			while (page < Math.ceil(req.count/1000)){
				page += 1;
				await new Promise(resolve => setTimeout(resolve, 25));
				const reqp = await makeRequest(`/api/items/page/${page}`, 'GET');
				items.push(...reqp.rows);
				commit('SET_LOADING', {
					curr: page,
					max: Math.ceil(req.count/1000)}
				);
			}
			const itemsshuffled = util.shuffle(items);
			commit('SET_ITEMS', itemsshuffled);
		},
	},
});

export default store;
