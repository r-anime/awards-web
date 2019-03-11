// Import libraries
import Vue from 'vue';
import router from './routes';

// What will be the Vue instance
let vm; // eslint-disable-line prefer-const

// Fetch user data and store it on the Vue instance when we get it
// NOTE: This is done as its own thing rather than in the Vue instance's
//       `created` hook because having the promise lets us use `await` to ensure
//       we have user data before navigating (which is important for when the
//       page is initially loading)
const loadPromise = fetch('/api/me').then(r => r.json()).then(user => {
	vm.user = user;
}).catch(console.error).finally(() => {
	vm.userLoaded = true;
});

// Handle authentication
router.beforeEach(async (to, from, next) => {
	// We must be loaded before we can route
	await loadPromise;
	if (to.path.startsWith('/host')) {
		if (vm.user) {
			// The user is logged in
			if (vm.user.isHost) {
				// The user is a host, so send them to the page
				return next();
			}
			// The user is not a host, so send them home
			return next('/');
		}
		// The user is not logged in, so have them log in
		return next('/login');
	// } else if (to.path.startsWith('/juror')) {
	// 	if (vm.user) {
	// 		// The user is logged in
	// 		if (vm.user.isHost) {
	// 			// The user is a juror, so send them to the page
	// 			return next();
	// 		}
	// 		// The user is not a juror, so send them home
	// 		return next('/');
	// 	}
	// 	// The user is not logged in, so have them log in
	// 	return next('/login');
	}
	next();
});

// Create the Vue instance
// NOTE: Not mounted yet because we have to register routing guards for
//       authentication before mounting the element. If we don't wait to mount,
//       the initial page load won't have the navigation guard registered in
//       time.
import App from './App';
vm = new Vue({
	// el: '#app',
	data: {
		user: null,
		userLoaded: false,
		userLoadedPromise: null,
	},
	router,
	render: create => create(App),
});

// Now that we have authentication set up, mount the Vue instance to the page
vm.$mount('#app');
