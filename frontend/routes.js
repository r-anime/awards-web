import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

// Layouts, pages, and routing
import PublicLayout from '../layouts/Public';
// import JurorLayout from './layout/Juror';
import HostLayout from '../layouts/Host';

import Home from '../pages/Home';
import About from '../pages/About';
import Profile from '../pages/Profile';
import JurorApplication from '../pages/JurorApplication';
import NotFound from '../pages/NotFound';
import Dashboard from '../pages/host/Dashboard';
import Categories from '../pages/host/Categories';
import Users from '../pages/host/Users';

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
				{path: 'apply', component: JurorApplication},
			],
		},
		// Layout for the host dashboard
		{
			path: '/host',
			component: HostLayout,
			children: [
				{path: '', component: Dashboard},
				{path: 'categories', component: Categories},
				{path: 'users', component: Users},
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
