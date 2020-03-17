/* eslint-disable */

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

// Layouts, pages, and routing
import PublicLayout from './layouts/Public';
// import JurorLayout from './layout/Juror';
import HostLayout from './layouts/Host';
import ResultLayout from './layouts/Result';

import Home from './pages/Home';
import About from './pages/About';
import Acknowledgements from './pages/Acknowledgements';
import Profile from './pages/Profile';
import ResultsPage from './pages/Results';
import ResultsContainer from './pages/ResultsContainer';
import ExtraAwardsPage from './pages/ExtraAwards';
import NotFound from './pages/NotFound';
import Archive from './pages/Archive';
import ArchiveLanding from './pages/ArchiveLanding';

import Login from './pages/HostLogin';
import Categories from './pages/host/Categories';
import Users from './pages/host/Users';
import Results from './pages/host/Results';
import AllCategories from './pages/host/AllCategories';
import SingleCategory from './components/SingleCategory';
import CategoryEntries from './components/CategoryEntries';
import CategoryNominations from './components/CategoryNominations';
import CategoryJurors from './components/CategoryJurors';
import CategoryHMs from './components/CategoryHMs';
import CategoryInfo from './components/CategoryInfo';
import CategoryTools from './components/CategoryTools';
import AdminPanel from './pages/host/AdminPanel';

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
		// login for hosts
		{
			path: '/login',
			component: PublicLayout,
			children: [
				{path: '', component: Login},
				{path: '/profile', component: Profile},
			],
		},

		// Layout for the host dashboard
		{
			path: '/host',
			component: HostLayout,
			meta: {
				title: 'Host Dashboard',
			},
			children: [
				{
					path: '',
					redirect: 'categories',
				},
				{
					path: 'categories',
					component: Categories,
					meta: {
						title: 'Categories',
					},
					children: [
						{
							name: 'allCategories',
							path: '',
							component: AllCategories,
						},
						{
							path: ':categoryId',
							component: SingleCategory,
							props: true,
							meta: {
								title ({$store, $route}) {
									if (!$store.state.categories) return '...';
									// eslint-disable-next-line eqeqeq
									const category = $store.state.categories.find(cat => `${cat.id}` == $route.params.categoryId);
									return category ? category.name : '(Unknown category)';
								},
							},
							children: [
								{
									name: 'category',
									path: '',
									redirect: 'info',
								},
								{
									name: 'categoryInfo',
									path: 'info',
									component: CategoryInfo,
									meta: {
										title: 'Information',
									},
								},
								{
									name: 'categoryEntries',
									path: 'entries',
									component: CategoryEntries,
									meta: {
										title: 'Entries',
									},
								},
								{
									name: 'categoryNominations',
									path: 'nominations',
									component: CategoryNominations,
									meta: {
										title: 'Nominations',
									},
								},
								{
									name: 'categoryJurors',
									path: 'jurors',
									component: CategoryJurors,
									meta: {
										title: 'Jurors',
									},
								},
								{
									name: 'categoryHMs',
									path: 'hms',
									component: CategoryHMs,
									meta: {
										title: 'Honorable Mentions',
									},
								},
								{
									name: 'categoryTools',
									path: 'tools',
									component: CategoryTools,
									meta: {
										title: 'Tools',
									},
								},
							],
						},
					],
				},
				{
					path: 'users',
					component: Users,
					meta: {
						title: 'Users',
					},
				},
				{
					path: 'admin',
					component: AdminPanel,
					meta: {
						title: 'Admin Panel',
					},
				},
				{
					path: 'results',
					component: Results,
					meta: {
						title: 'Results Summary',
					},
				},
			],
		},

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
