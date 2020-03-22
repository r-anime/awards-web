// Import style stuff
import './styles/utilities.scss';

// Import libraries
import Vue from 'vue';
import store from './store';
import router from './voteRoutes';

import App from './VoteApp';

const vm = new Vue({
	store,
	router,
	render: create => create(App),
});

vm.$mount('#app');
