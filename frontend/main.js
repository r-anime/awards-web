// Import style stuff
import './styles/main.scss';

// Import libraries
import Vue from 'vue';
import router from './routes';

// What will be the Vue instance
let vm; // eslint-disable-line prefer-const

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
	}
}

// Fetch core user data and store it on the Vue instance when we get it
// NOTE: This is done as its own thing rather than in the Vue instance's
//       `created` hook because having the promise lets us use `await` to ensure
//       we have user data before navigating (which is important for when the
//       page is initially loading)
const loadPromise = makeRequest('/api/me').then(user => {
	vm.me = user;
}).finally(() => {
	vm.loaded = true;
});

// Handle authentication on page navigation
router.beforeEach(async (to, from, next) => {
	// We must have the user info loaded before we can route
	await loadPromise;
	if (to.path.startsWith('/host')) {
		if (!vm.me) {
			// The user is not logged in, so have them log in
			return next('/login');
		}
		if (vm.me.level >= 2) {
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
vm = new Vue({
	// el: '#app',
	data: {
		me: null,
		loaded: false,

		users: null,
		categories: null,
	},
	computed: {
		// Auth helpers
		isHost () {
			return this.me && this.me.level >= 2;
		},
		isMod () {
			return this.me && this.me.level >= 3;
		},
		isAdmin () {
			return this.me && this.me.level >= 4;
		},
	},
	methods: {
		getUsers () {
			return makeRequest('/api/users').then(users => {
				this.users = users;
			});
		},
		addUser (data) {
			return makeRequest('/api/user', 'POST', data).then(user => {
				this.users.push(user);
			});
		},
		removeUser (reddit) {
			return makeRequest(`/api/user/${reddit}`, 'DELETE').then(() => {
				const targetIndex = this.users.findIndex(user => user.reddit === reddit);
				this.users.splice(targetIndex, 1);
			});
		},

		getCategories () {
			return makeRequest('/api/categories').then(categories => {
				this.categories = categories;
			});
		},
		createCategory (data) {
			return makeRequest('/api/category', 'POST', data).then(category => {
				this.categories.push(category);
			});
		},
		deleteCategory (id) {
			return makeRequest(`/api/category/${id}`, 'DELETE').then(() => {
				const targetIndex = this.categories.findIndex(cat => cat.id === id);
				this.categories.splice(targetIndex, 1);
			});
		},
	},
	router,
	render: create => create(App),
});

// Now that we have authentication set up, mount the Vue instance to the page
vm.$mount('#app');
