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
		window.alert(error);
		throw error;
	}
}

const store = new Vuex.Store({
	state: {
		locks: null,
		categories: null,
		entries: null,
	},
	mutations: {
		GET_CATEGORIES (state, categories) {
			state.categories = categories;
		},
		GET_ENTRIES (state, entries) {
			state.entries = entries;
		},
		GET_LOCKS (state, locks) {
			state.locks = locks;
		},
	},
	actions: {
		async getCategories ({commit}) {
			const categories = await makeRequest('/api/category/all');
			commit('GET_CATEGORIES', categories);
		},
		async getEntries ({commit}) {
			const entries = await makeRequest('/api/category/entries/vote');
			commit('GET_ENTRIES', entries);
		},
		async getLocks ({commit}) {
			const locks = await makeRequest('/api/locks/all');
			commit('GET_LOCKS', locks);
		},
		async sendAnalytics ({commit}, data) {
			const width = window.innerWidth;
			const url = window.location.href;
			const meta = data.meta || null;

			const payload = {
				n: data.name,
				u: url,
				d: 'animeawards.moe',
				r: data.referrer,
				w: width,
				p: meta,
			};
			
			const votes = await makeRequest('/api/analytics/send', 'POST', payload);
		},
	},
});

export default store;
