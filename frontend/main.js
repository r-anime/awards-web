// Import libraries
import Vue from 'vue';
import store from './store';
import router from './routes';

// fontawesome stuff
import {library} from '@fortawesome/fontawesome-svg-core';
import {faBook, faUsers, faUserFriends, faPalette, faPaintBrush, faPencilRuler, faCog, faCrown, faInfoCircle, faFileInvoice, faComment} from '@fortawesome/free-solid-svg-icons';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

library.add(faBook, faUsers, faUserFriends, faPalette, faPaintBrush, faPencilRuler, faCog, faCrown, faInfoCircle, faFileInvoice, faComment);

import App from './App';

Vue.component('fa-icon', FontAwesomeIcon);

const vm = new Vue({
	store,
	router,
	render: create => create(App),
});

vm.$mount('#app');
