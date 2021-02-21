const resultsRoutes = [
	{name: 'Genre', path: '/results/genre'},
	{name: 'Character', path: '/results/character'},
	{name: 'Production', path: '/results/production'},
	{name: 'Main', path: '/results/main'},
	{name: 'View All', path: '/results/all'},
	{name: 'Past Results', path: '/archive'},
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
