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
import AllCategories from './pages/host/AllCategories';
import SingleCategory from './components/SingleCategory';
import CategoryEntries from './components/CategoryEntries';
import CategoryAbout from './components/CategoryAbout';

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
			children: [
				{path: '', redirect: 'categories'},
				{
					path: 'categories',
					component: Categories,
					meta: {
						title: 'Categories',
					},
					children: [
						{
							path: '',
							component: AllCategories,
						},
						{
							path: ':categoryId',
							component: SingleCategory,
							props: true,
							meta: {
								title: 'Category name TODO',
							},
							children: [
								{
									path: '',
									redirect: 'about',
								},
								{
									path: 'about',
									component: CategoryAbout,
									meta: {
										title: 'Settings',
									},
								},
								{
									path: 'entries',
									component: CategoryEntries,
									meta: {
										title: 'Entries',
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
		// // Layout for juror things
		// {
		// 	path: '/juror',
		// 	component: JurorLayout,
		// 	children: [],
		// },

		// 404 route - keep last
		{path: '*', component: NotFound},
	],
});
