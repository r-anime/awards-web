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
		me: null,
		locks: null,
		application: null,
		applicant: null,
		myAnswers: null,
	},
	mutations: {
		GET_ME (state, me) {
			state.me = me;
		},
		GET_LOCKS (state, locks) {
			state.locks = locks;
		},
		GET_APPLICATION (state, app) {
			state.application = app;
		},
		GET_APPLICANT (state, applicant) {
			state.applicant = applicant;
		},
		GET_ANSWERS (state, answers) {
			state.myAnswers = answers;
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
		async getApplication ({commit}) {
			const application = await makeRequest('/api/juror-apps/applications/latest/full');
			commit('GET_APPLICATION', application);
		},
		async getApplicant ({commit}) {
			const applicant = await makeRequest('/api/juror-apps/applicant');
			commit('GET_APPLICANT', applicant);
		},
		async getAnswers ({commit}, applicantID) {
			const answers = await makeRequest(`/api/juror-apps/my-answers/${applicantID}`);
			commit('GET_ANSWERS', answers);
		},
	},
});

export default store;
