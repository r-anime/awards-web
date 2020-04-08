/* eslint-disable */

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

import NotFound from '../common/NotFound';

import Voting from './pages/Voting';
import Instructions from './pages/Instructions';

import GroupDisplay from './components/GroupDisplay';


export default new VueRouter({
	mode: 'history',
	scrollBehavior(to, from, savedPosition) {
		document.body.scrollTop = 0; // For Safari
    	document.documentElement.scrollTop = 0;
	},
	routes: [
		// keep this here for now
		// Stuff for nomination things
		{
			path: '/vote',
			component: Voting,
			redirect: '/vote/main',
			children: [
				{
					path: ':group',
					component: GroupDisplay,
					name: 'GroupDisplay',
					props: true,
				},
				{
					path: 'instructions',
					component: Instructions,
					name: 'Instructions',
				},
			],
		},

		// 404 route - keep last
		{path: '*', component: NotFound},
	],
});
