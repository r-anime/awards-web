/* eslint-disable */

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

import AppsLayout from './AppsLayout';

import Profile from '../common/Profile';
import AppPage from './pages/AppPage';

import NotFound from '../common/NotFound';

export default new VueRouter({
	mode: 'history',
	scrollBehavior(to, from, savedPosition) {
		document.body.scrollTop = 0; // For Safari
    	document.documentElement.scrollTop = 0;
	},
	routes: [
		// Default layout
		{
			path: '/apps',
			component: AppsLayout,
			children: [
				{path: '', component: AppPage},
				{path: 'profile', component: Profile},
			],
		},
		// 404 route - keep last
		{path: '*', component: NotFound},
	],
});
