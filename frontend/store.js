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
		window.alert(error);
		throw error;
	}
}

const store = new Vuex.Store({
	state: {
		me: null,
		users: null,
		categories: null,
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
		DELETE_CATEGORY (state, id) {
			const index = state.categories.findIndex(cat => cat.id === id);
			state.categories.splice(index, 1);
		},
	},
	actions: {
		async getMe ({commit}) {
			const me = await makeRequest('/api/me');
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
		async createCategory ({commit}, category) {
			const finalCategory = await makeRequest('/api/category', 'POST', category);
			commit('CREATE_CATEGORY', finalCategory);
		},
		async deleteCategory ({commit}, id) {
			await makeRequest(`/api/category/${id}`, 'DELETE');
			commit('DELETE_CATEGORY', id);
		},
	},
});

export default store;
