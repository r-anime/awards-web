// Import style stuff
import './styles/main.scss';

// Import libraries
import Vue from 'vue';
import store from './store';
import router from './routes';

// Fetch core user data and store it on the Vue instance when we get it
// NOTE: This is done as its own thing rather than in the Vue instance's
//       `created` hook because having the promise lets us use `await` to ensure
//       we have user data before navigating (which is important for when the
//       page is initially loading)
const loadPromise = store.dispatch('getMe');

// Handle authentication on page navigation
router.beforeEach(async (to, from, next) => {
	// We must have the user info loaded before we can route
	await loadPromise;
	if (to.path.startsWith('/host')) {
		if (!store.state.me) {
			// The user is not logged in, so have them log in
			return next('/');
		}
		if (store.state.me.level >= 2) {
			// The user is a host, so send them to the page
			return next();
		}
		// The user is not a host, so send them home
		return next('/');
	}
	next();
});

// Create the Vue instance
// NOTE: Not mounted yet because we have to register routing guards for
//       authentication before mounting the element. If we don't wait to mount,
//       the initial page load won't have the navigation guard registered in
//       time.
import App from './App';
const vm = new Vue({
	store,
	router,
	render: create => create(App),
});

// Now that we have authentication set up, mount the Vue instance to the page
vm.$mount('#app');
