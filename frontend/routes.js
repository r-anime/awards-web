/* eslint-disable */

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

// Layouts, pages, and routing
const PublicLayout = () => import(/* webpackChunkName: "host" */ './layouts/Public');
// import JurorLayout from './layout/Juror';
const HostLayout = () => import(/* webpackChunkName: "host" */ './layouts/Host');
const ResultLayout = () => import(/* webpackChunkName: "core" */ './layouts/Result');

const Home = () => import(/* webpackChunkName: "core" */ './pages/Home');
const About = () => import(/* webpackChunkName: "core" */ './pages/About');
const Profile = () => import(/* webpackChunkName: "core" */ './pages/Profile');
const ResultsPage = () => import(/* webpackChunkName: "core" */ './pages/Results');
const NotFound = () => import(/* webpackChunkName: "core" */ './pages/NotFound');

const Login = () => import(/* webpackChunkName: "host" */ './pages/HostLogin');
const Categories = () => import(/* webpackChunkName: "host" */ './pages/host/Categories');
const Users = () => import(/* webpackChunkName: "host" */ './pages/host/Users');
const Results = () => import(/* webpackChunkName: "host" */ './pages/host/Results');
const AllCategories = () => import(/* webpackChunkName: "host" */ './pages/host/AllCategories');
const SingleCategory = () => import(/* webpackChunkName: "host" */ './components/SingleCategory');
const CategoryEntries = () => import(/* webpackChunkName: "host" */ './components/CategoryEntries');
const CategoryNominations = () => import(/* webpackChunkName: "host" */ './components/CategoryNominations');
const CategoryJurors = () => import(/* webpackChunkName: "host" */ './components/CategoryJurors');
const CategoryHMs = () => import(/* webpackChunkName: "host" */ './components/CategoryHMs');
const CategoryInfo = () => import(/* webpackChunkName: "host" */ './components/CategoryInfo');
const CategoryTools = () => import(/* webpackChunkName: "host" */ './components/CategoryTools');

const Voting = () => import(/* webpackChunkName: "vote" */ './pages/Voting');
const GroupDisplay = () => import(/* webpackChunkName: "vote" */ './voting/GroupDisplay');
const Instructions = () => import(/* webpackChunkName: "vote" */ './voting/Instructions');



export default new VueRouter({
	mode: 'history',
	routes: [
		// Default layout
		{
			path: '/',
			component: ResultLayout, // when the home component is redesigned, this component will be changed to home
			children: [
				{path: '', component: Home}, // these won't be children of the new Home component, only results/genre etc. would be
				{path: 'about', component: About},
				{path: 'results', component: ResultsPage},
			],
		},
		// login for hosts
		{
			path: '/login',
			component: PublicLayout,
			children: [
				{path: '', component: Login},
				{path: '/profile', component: Profile},
				{path: '/about', component: About},
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
