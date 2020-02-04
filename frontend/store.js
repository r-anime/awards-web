import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const util = require('./util');
const queries = require('./voting/anilistQueries');

const maxAccountDate = 1578009600; // sync with util/helpers.js for backend

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
		users: null,
		categories: null,
		themes: null,
		votingCats: null,
		selections: null,
		voteSummary: null,
		voteTotals: null,
		opedTotals: null,
		dashTotals: null,
		nominations: null,
	},
	getters: {
		isHost (state) {
			return state.me && state.me.level >= 2;
		},
		isMod (state) {
			return state.me && state.me.level >= 3;
		},
		isAdmin (state) {
			return state.me && state.me.level >= 4;
		},
		accountOldEnough (state) {
			return state.me && state.me.reddit && state.me.reddit.created < maxAccountDate;
		},
	},
	mutations: {
		GET_ME (state, me) {
			state.me = me;
		},

		GET_USERS (state, users) {
			state.users = users;
		},
		ADD_USER (state, user) {
			state.users.push(user);
		},
		REMOVE_USER (state, reddit) {
			const index = state.users.findIndex(user => user.reddit === reddit);
			state.users.splice(index, 1);
		},
		GET_CATEGORIES (state, categories) {
			state.categories = categories;
		},
		CREATE_CATEGORY (state, category) {
			state.categories.push(category);
		},
		UPDATE_CATEGORY (state, category) {
			const index = state.categories.findIndex(cat => cat.id === category.id);
			state.categories.splice(index, 1, category);
		},
		DELETE_CATEGORY (state, categoryId) {
			const index = state.categories.findIndex(cat => cat.id === categoryId);
			state.categories.splice(index, 1);
		},
		UPDATE_THEMES (state, themes) {
			state.themes = themes;
		},
		GET_VOTING_CATEGORIES (state, votingCats) {
			state.votingCats = votingCats;
		},
		UPDATE_SELECTIONS (state, selections) {
			state.selections = selections;
		},
		GET_VOTE_SUMMARY (state, voteSummary) {
			state.voteSummary = voteSummary;
		},
		GET_VOTE_TOTALS (state, voteTotals) {
			state.voteTotals = voteTotals;
		},
		GET_DASHBOARD_TOTALS (state, dashTotals) {
			state.dashTotals = dashTotals;
		},
		GET_OPED_TOTALS (state, opedTotals) {
			state.opedTotals = opedTotals;
		},
		GET_NOMINATIONS (state, nominations) {
			state.nominations = nominations;
		},
		INSERT_NOMINATION (state, nomination) {
			state.nominations.push(nomination);
		},
		DELETE_NOMINATIONS (state) {
			state.nominations = [];
		},
		UPDATE_NOMINATIONS (state, nominations) {
			state.nominations = nominations;
		},
	},
	actions: {
		async getMe ({commit}) {
			const response = await fetch('/api/me');
			if (!response.ok) return;
			const me = await response.json();
			commit('GET_ME', me);
		},

		async getUsers ({commit}) {
			const users = await makeRequest('/api/users');
			commit('GET_USERS', users);
		},
		async addUser ({commit}, user) {
			const finalUser = await makeRequest('/api/user', 'POST', user);
			commit('ADD_USER', finalUser);
		},
		async removeUser ({commit}, reddit) {
			await makeRequest(`/api/user/${reddit}`, 'DELETE');
			commit('REMOVE_USER', reddit);
		},

		async getCategories ({commit}) {
			const categories = await makeRequest('/api/categories');
			commit('GET_CATEGORIES', categories);
		},
		async createCategory ({commit}, {data}) {
			const category = await makeRequest('/api/category', 'POST', data);
			commit('CREATE_CATEGORY', category);
		},
		async updateCategory ({commit}, {id, data}) {
			console.log(id, data);
			// TODO: I don't like that the id and the data are in the same object here
			const updatedCategoryData = await makeRequest(`/api/category/${id}`, 'PATCH', {id, ...data});
			commit('UPDATE_CATEGORY', updatedCategoryData);
		},
		async deleteCategory ({commit}, categoryId) {
			await makeRequest(`/api/category/${categoryId}`, 'DELETE');
			commit('DELETE_CATEGORY', categoryId);
		},
		async getThemes ({commit}) {
			const themes = await makeRequest('/api/themes');
			commit('UPDATE_THEMES', themes);
		},
		async createThemes ({commit}, {data}) {
			const themes = await makeRequest('/api/themes/create', 'POST', data);
			commit('UPDATE_THEMES', themes);
		},
		async deleteThemes ({commit}, themeType) {
			const themes = await makeRequest(`/api/themes/delete/${themeType}`, 'DELETE');
			commit('UPDATE_THEMES', themes);
		},
		async getVotingCategories ({commit, state, dispatch}, group) {
			if (!state.categories) {
				await dispatch('getCategories');
			}
			const votingCats = state.categories.filter(cat => cat.awardsGroup === group);
			commit('GET_VOTING_CATEGORIES', votingCats);
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
					if (vote.anilist_id && !vote.theme_name) { // This condition is fulfilled for dashboard cats only
						// Dashboard categories have their anilist stored elsewhere
						allIDs.shows.push(vote.anilist_id);
					} else if (vote.theme_name) {
						// Theme category so we're gonna push the whole theme and SQUASH this shit later
						const theme = state.themes.find(themeData => themeData.id === vote.entry_id);
						themeObject[theme.themeType].push(theme);
					} else if (category.entryType === 'characters') {
						// All of these are pushing anilist IDs into a bunch of arrays for querying
						allIDs.chars.push(vote.entry_id);
					} else if (category.entryType === 'vas') {
						allIDs.vas.push(vote.entry_id);
					} else if (category.entryType === 'shows') {
						allIDs.shows.push(vote.entry_id);
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
		async getVoteSummary ({commit}) {
			const voteSummary = await makeRequest('/api/voteSummary');
			commit('GET_VOTE_SUMMARY', voteSummary);
		},
		async getVoteTotals ({commit}) {
			const voteTotals = await makeRequest('/api/votes/all/get');
			commit('GET_VOTE_TOTALS', voteTotals);
		},
		async getDashboardTotals ({commit}) {
			const dashTotals = await makeRequest('/api/votes/dashboard/get');
			commit('GET_DASHBOARD_TOTALS', dashTotals);
		},
		async getOPEDTotals ({commit}) {
			const opedTotals = await makeRequest('/api/votes/oped/get');
			commit('GET_OPED_TOTALS', opedTotals);
		},
		async getNominations ({commit}, categoryId) {
			const noms = await makeRequest(`/api/category/${categoryId}/nominations`);
			commit('GET_NOMINATIONS', noms);
		},
		async insertNomination ({commit}, {id, nomination}) {
			const nom = await makeRequest(`/api/category/${id}/nomination`, 'POST', nomination);
			commit('INSERT_NOMINATION', nom);
		},
		async deleteNominations ({commit}, categoryId) {
			await makeRequest(`/api/category/${categoryId}/nominations`, 'DELETE');
			commit('DELETE_NOMINATIONS');
		},
		async updateNominations ({commit}, {categoryId, data}) {
			await makeRequest(`/api/category/${categoryId}`, 'PATCH', {categoryId, ...data});
			commit('UPDATE_NOMINATIONS');
		},
	},
});

export default store;
