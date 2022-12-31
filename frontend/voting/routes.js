/* eslint-disable */

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

import NotFound from '../common/NotFound';
import Profile from '../common/Profile';

import Voting from './pages/Voting';
import Instructions from './pages/Instructions';

import myVotes from "./components/myVotes";
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
					path: 'profile',
					component: Profile,
					name: 'Profile',
				},
				{
					path: 'myVotes',
					component: myVotes,
					name: 'myVotes',
				},
				{
					path: ':group',
					component: GroupDisplay,
					name: 'GroupDisplay',
					props: true,
				},
				//HERE
				
			],
		},

		// 404 route - keep last
		{path: '*', component: NotFound},
	],
});
