/* eslint-disable */

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

import MainLayout from './pages/MainLayout';
import FinalVote from './pages/FinalVote';
import FinalVoteEnd from './pages/FinalVoteEnd';

import NotFound from '../common/NotFound';
import Profile from '../common/Profile';


export default new VueRouter({
	mode: 'history',
	scrollBehavior(to, from, savedPosition) {
		document.body.scrollTop = 0; // For Safari
    	document.documentElement.scrollTop = 0;
	},
	routes: [
		{
			path: '/final-vote',
			component: MainLayout,
			children: [
				{
					path: '',
					component: FinalVote,
				},
				{
					path: 'thanks',
					component: FinalVoteEnd,
					name: 'thankspage',
				},
				{
					path: 'profile',
					component: Profile,
				},
			],
		},
		// 404 route - keep last
		{path: '*', component: NotFound},
	],
});
