const resultsRoutes = [
	{name: 'Results', path: '/results/all', children: [
		{name: 'Genre', path: '/results/genre'},
		{name: 'Production', path: '/results/production'},
		{name: 'Main', path: '/results/main'},
	]},
	{name: 'Archive', path: '/archive'},
	{name: 'Acknowledgements', path: '/acknowledgements'},
	{name: 'About', path: '/about'},
	{name: 'Feedback', path: '/feedback'},
];

const ongoingRoutes = [
	{name: 'Past Results', path: '/archive'},
	{name: 'About', path: '/about'},
	{name: 'Feedback', path: '/feedback'},
];

module.exports = {
	resultsRoutes,
	ongoingRoutes,
};
