/* eslint-disable */

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

// Layouts, pages, and routing
import ResultLayout from './layouts/Result';

import Home from './pages/Home';
import About from './pages/About';
import Acknowledgements from './pages/Acknowledgements';
import ResultsPage from './pages/Results';
import ResultsContainer from './pages/ResultsContainer';
import ExtraAwardsPage from './pages/ExtraAwards';
import NotFound from './pages/NotFound';
import Archive from './pages/Archive';
import ArchiveLanding from './pages/ArchiveLanding';

import Voting from './pages/Voting';
import GroupDisplay from './voting/GroupDisplay';
import Instructions from './voting/Instructions';

export default new VueRouter({
	mode: 'history',
	scrollBehavior(to, from, savedPosition) {
		document.body.scrollTop = 0; // For Safari
    	document.documentElement.scrollTop = 0;
	},
	routes: [
		// Default layout
		{
			path: '/',
			component: ResultLayout,
			children: [
				{path: '', component: Home},
				{path: 'thanks', component: About},
				{
					path: 'results',
					redirect: 'results/all',
					component: ResultsContainer,
					children: [
						{
							path: ':slug',
							component: ResultsPage,
							props: true,
						},
					],
				},
				{path: '/acknowledgements', component: Acknowledgements},
				{path: '/about', component: About},
				{path: '/extra', component: ExtraAwardsPage},
				{
					path: '/archive',
					component: Archive,
					children: [
						{
							path: '',
							component: ArchiveLanding,
						},
						{
							path: ':year',
							component: ResultsPage,
							props: true,
						},
					]
				},
			],
		},

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
