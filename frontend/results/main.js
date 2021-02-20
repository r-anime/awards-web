// Import libraries
import Vue from 'vue';
import router from './routes';
import store from './store';
//  import { VuePlausible } from 'vue-plausible'

// fontawesome stuff
import {library} from '@fortawesome/fontawesome-svg-core';
import {faBook, faUsers, faUserFriends, faPalette, faPaintBrush, faPencilRuler, faCog, faCrown, faInfoCircle, faFileInvoice, faComment} from '@fortawesome/free-solid-svg-icons';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

library.add(faBook, faUsers, faUserFriends, faPalette, faPaintBrush, faPencilRuler, faCog, faCrown, faInfoCircle, faFileInvoice, faComment);

import App from './App';

Vue.component('fa-icon', FontAwesomeIcon);

// Vue.use(VuePlausible, {
// 	domain: 'animeawards.moe',
// 	trackLocalhost: true,
// })

// Vue.$plausible.enableAutoPageviews();

const vm = new Vue({
	router,
	store,
	render: create => create(App),
});

vm.$mount('#app');
