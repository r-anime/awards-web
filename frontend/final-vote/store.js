/* eslint-disable no-unused-vars */
/* eslint-disable no-await-in-loop */
import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

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
		throw error;
	}
}

const store = new Vuex.Store({
	state: {
		me: null,
		locks: null,
		categories: null,
		nominations: null,
		themes: null,
		votes: null,
		survey: null,
	},
	mutations: {
		GET_ME (state, me) {
			state.me = me;
		},
		GET_LOCKS (state, locks) {
			state.locks = locks;
		},
		GET_CATEGORIES (state, categories) {
			state.categories = categories;
		},
		GET_NOMINATIONS (state, noms) {
			state.nominations = noms;
		},
		UPDATE_THEMES (state, themes) {
			state.themes = themes;
		},
		UPDATE_VOTES (state, votes) {
			state.votes = votes;
		},
		UPDATE_SURVEY (state, survey) {
			state.survey = survey;
		},
	},
	actions: {
		async getMe ({commit}) {
			const response = await fetch('/api/user/me');
			if (!response.ok) return;
			const me = await response.json();
			commit('GET_ME', me);
		},
		async getLocks ({commit}) {
			const locks = await makeRequest('/api/locks/all');
			commit('GET_LOCKS', locks);
		},
		async getCategories ({commit}) {
			const categories = await makeRequest('/api/category/all');
			commit('GET_CATEGORIES', categories);
		},
		async getNominations ({commit}) {
			const noms = await makeRequest('/api/category/nominations/all');
			commit('GET_NOMINATIONS', noms);
		},
		async getThemes ({commit}) {
			const themes = await makeRequest('/api/themes');
			commit('UPDATE_THEMES', themes);
		},
		async getVotes ({commit, state, dispatch}) {
			if (!state.locks) {
				await dispatch('getLocks');
			}
			if (!state.me) {
				await dispatch('getMe');
			}
			const _gl = state.locks.find(lock => lock.name === 'fv-genre');
			if (_gl.flag || state.me.level > _gl.level) {
				const votes = await makeRequest('/api/final/get');
				commit('UPDATE_VOTES', votes);
			} else {
				commit('UPDATE_VOTES', []);
			}
		},
		async submitVote ({commit}, data) {
			const votes = await makeRequest('/api/final/submit', 'POST', data);
			commit('UPDATE_VOTES', votes);
		},
		async submitVoteDelete ({commit}, data) {
			const votes = await makeRequest('/api/final/submit/delete', 'POST', data);
			commit('UPDATE_VOTES', votes);
		},
		async getSurvey ({commit, state, dispatch}) {
			if (!state.locks) {
				await dispatch('getLocks');
			}
			if (!state.me) {
				await dispatch('getMe');
			}
			const _gl = state.locks.find(lock => lock.name === 'fv-genre');
			if (_gl.flag || state.me.level > _gl.level) {
				const votes = await makeRequest('/api/final/survey/get');
				commit('UPDATE_SURVEY', votes);
			} else {
				commit('UPDATE_SURVEY', []);
			}
		},
		async submitSurvey ({commit}, data) {
			const votes = await makeRequest('/api/final/survey/submit', 'POST', data);
			commit('UPDATE_SURVEY', votes);
		},
	},
});

export default store;
