// Import libraries
import Vue from 'vue';
import router from './routes';
import store from './store';

// Handle authentication on page navigation
router.beforeEach(async (to, from, next) => {
	// We must have the user info loaded before we can route
	if (to.path.startsWith('/apps')) {
		// Fetch core user data and store it on the Vue instance when we get it
		// NOTE: This is done as its own thing rather than in the Vue instance's
		//       `created` hook because having the promise lets us use `await` to ensure
		//       we have user data before navigating (which is important for when the
		//       page is initially loading)
		if (!store.state.me) await store.dispatch('getMe');
		if (!store.state.me) {
			// The user is not logged in, so have them log in
			return next('/auth/reddit/apps');
		}
		// The user is logged in, send them to the next page
		return next();
	}
	next();
});

import App from './App';

const vm = new Vue({
	router,
	store,
	render: create => create(App),
});

vm.$mount('#app');
