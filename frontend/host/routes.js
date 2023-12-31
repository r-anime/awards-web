/* eslint-disable */

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

// Layouts, pages, and routing
import PublicLayout from './PublicLayout';
import HostLayout from './HostLayout';

import Profile from '../common/Profile';
import Login from './pages/HostLogin';
import LoginRedirect from './pages/LoginRedirect';
import Categories from './pages/Categories';
import Users from './pages/Users';
import Results from './pages/Results';
import FinalResults from './pages/FinalResults';
import AllCategories from './pages/AllCategories';
import CategorySort from './pages/CategorySort';
import AdminPanel from './pages/AdminPanel';
import Applications from './pages/Applications';
import ManageItems from './pages/ManageItems';
import AllApplications from './pages/AllApplications';
import Allocations from './pages/Allocations';

import SingleCategory from './components/SingleCategory';
import CategoryEntries from './components/CategoryEntries';
import CategoryNominations from './components/CategoryNominations';
import CategoryJurors from './components/CategoryJurors';
import CategoryHMs from './components/CategoryHMs';
import CategoryInfo from './components/CategoryInfo';
import CategoryTools from './components/CategoryTools';
import SingleApplication from './components/SingleApplication';
import ApplicationQuestions from './components/ApplicationQuestions';
import ApplicationGrading from './components/ApplicationGrading';
import ApplicationInfo from './components/ApplicationInfo';
import Applicants from './components/Applicants';
import SingleApplicant from './components/SingleApplicant';
import ApplicationGradingTable from './components/ApplicationGradingTable';
import ApplicantRanking from './components/ApplicantRanking';
import Preferences from './components/Preferences';

import NotFound from '../common/NotFound';

export default new VueRouter({
	mode: 'history',
	scrollBehavior(to, from, savedPosition) {
		document.body.scrollTop = 0; // For Safari
    	document.documentElement.scrollTop = 0;
	},
	routes: [
		// login for hosts
		{
			path: '/login',
			component: PublicLayout,
			children: [
				{path: '', component: Login},
				{path: 'profile', component: Profile},
				{
					path: 'redirect/:next',
					props: true,
					component: LoginRedirect
				},
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
							name: 'sortCategories',
							path: 'sort',
							component: CategorySort,
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
					path: 'applications',
					meta: {
						title: 'Jury Applications',
					},
					component: Applications,
					children: [
						{
							path: '',
							component: AllApplications,
						},
						{
							path: ':appID',
							props: true,
							component: SingleApplication,
							meta: {
								title ({$store, $route}) {
									if (!$store.state.applications) return '...';
									// eslint-disable-next-line eqeqeq
									const application = $store.state.applications.find(app => `${app.id}` == parseInt($route.params.appID));
									return application ? application.year : '(Unknown application)';
								},
							},
							children: [
								{
									path: '',
									redirect: 'info',
								},
								{
									path: 'info',
									meta: {
										title: 'Information',
									},
									component: ApplicationInfo,
								},
								{
									path: 'questions',
									meta: {
										title: 'Questions',
									},
									component: ApplicationQuestions,
								},
								{
									path: 'grading',
									meta: {
										title: 'Grading',
									},
									component: ApplicationGrading,
								},
								{
									path: 'grading-table',
									meta: {
										title: 'Grading Table',
									},
									component: ApplicationGradingTable,
								},
								{
									path: 'juror-ranks',
									meta: {
										title: 'Juror Ranking',
									},
									component: ApplicantRanking,
								},
								{
									path: 'applicants',
									meta: {
										title: 'Applicants',
									},
									component: Applicants,
								},
								{
									path: 'preferences',
									meta: {
										title: 'Preferences',
									},
									component: Preferences,
								},
								{
									name: 'applicant',
									path: 'applicant/:applicantID',
									props: true,
									component: SingleApplicant,
									meta: {
										title ({$store, $route}) {
											if (!$store.state.applicants) return '...';
											// eslint-disable-next-line eqeqeq
											const applicant = $store.state.applicants.find(applicant => `${applicant.id}` == $route.params.applicantID);
											return applicant ? applicant.id : '(Unknown applicant)';
										}
									},
								}
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
					path: 'items',
					component: ManageItems,
					meta: {
						title: 'Items',
					},
				},
				{
					path: 'results',
					component: Results,
					meta: {
						title: 'Results (Initial)',
					},
				},
				{
					path: 'final-results',
					component: FinalResults,
					meta: {
						title: 'Results (Final)',
					},
				},
				{
					path: 'allocations',
					component: Allocations,
					meta: {
						title: 'Allocations',
					},
				},
			],
		},

		// 404 route - keep last
		{path: '*', component: NotFound},
	],
});
