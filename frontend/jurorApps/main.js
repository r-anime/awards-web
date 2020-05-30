// Import libraries
import Vue from 'vue';
import router from './routes';

import App from './App';

const vm = new Vue({
	router,
	render: create => create(App),
});

vm.$mount('#app');
