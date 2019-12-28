import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

// Layouts, pages, and routing
import PublicLayout from './layouts/Public';
// import JurorLayout from './layout/Juror';
import HostLayout from './layouts/Host';

import Home from './pages/Home';
import About from './pages/About';
import Profile from './pages/Profile';
import NotFound from './pages/NotFound';
import Categories from './pages/host/Categories';
import Users from './pages/host/Users';
import Voting from './pages/Voting';
import AllCategories from './pages/host/AllCategories';
import SingleCategory from './components/SingleCategory';
import CategoryEntries from './components/CategoryEntries';
import CategoryInfo from './components/CategoryInfo';
import CategoryTools from './components/CategoryTools';

import GroupDisplay from './voting/GroupDisplay';

export default new VueRouter({
	mode: 'history',
	routes: [
		// Default layout
		{
			path: '/',
			component: PublicLayout,
			children: [
				{path: '', component: Home},
				{path: 'profile', component: Profile},
				{path: 'about', component: About},
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
			],
		},

		// 404 route - keep last
		{path: '*', component: NotFound},
	],
});
