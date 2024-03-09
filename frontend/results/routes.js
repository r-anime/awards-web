/* eslint-disable */

import Vue from 'vue';
import VueRouter from 'vue-router';
import store from './store.js';

Vue.use(VueRouter);

// Layouts, pages, and routing
import ResultLayout from './ResultLayout';

import Home from './pages/Home';
import About from './pages/About';
import Acknowledgements from './pages/Acknowledgements';
import ResultsPage from './pages/Results';
import ArchiveLanding from './pages/ArchiveLanding';
import Allocations from './pages/Allocations';
import JuryGuide from './pages/JuryGuide';
import Feedback from './pages/Feedback';

import ResultsContainer from './components/ResultsContainer';
import Archive from './components/Archive';

import NotFound from '../common/NotFound';

// Archived pages
import Acknowledgements19 from './archived-pages/Acknowledgements19';
import About19 from './archived-pages/About19';
import Acknowledgements20 from './archived-pages/Acknowledgements20';
import About20 from './archived-pages/About20';
import Acknowledgements21 from './archived-pages/Acknowledgements21';
import Acknowledgements22 from './archived-pages/Acknowledgements22';
import About21 from './archived-pages/About21';
import About22 from './archived-pages/About22';

const router = new VueRouter({
	mode: 'history',
	scrollBehavior (to, from, savedPosition) {
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
				{path: '/thanks', component: About},
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
				{path: '/allocations', component: Allocations},
				{path: '/feedback', component: Feedback},
				{path: '/juryguide', component: JuryGuide},
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
							redirect: ':year/all',
							component: ResultsPage,
							props: true,
							children: [
								{
									path: ':slug',
									component: ResultsPage,
									props: true,
								},
							],
						},
					]
				},
				// Here we shall keep track of archived vue components that will be added to each year.
				//2019
				{path: '/acknowledgements19', component: Acknowledgements19},
				{path: '/acknowledgements20', component: Acknowledgements20},
				{path: '/acknowledgements21', component: Acknowledgements21},
				{path: '/acknowledgements22', component: Acknowledgements22},
				{path: '/about19', component: About19},
				{path: '/about20', component: About20},
				{path: '/about21', component: About21},
				{path: '/about22', component: About22},
			],
		},

		// 404 route - keep last
		{path: '*', component: NotFound},
	],
});

// router.afterEach(async (to, from) => {
//     await store.dispatch('sendAnalytics', {name: 'pageview', url: to.fullPath, referrer: from.fullPath});
// })

export default router;
