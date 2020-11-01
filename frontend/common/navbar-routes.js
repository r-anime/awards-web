const resultsRoutes = [
	{name: 'Genre', path: '/results/genre'},
	{name: 'Character', path: '/results/character'},
	{name: 'Production', path: '/results/production'},
	{name: 'Main', path: '/results/main'},
	{name: 'Test', path: '/results/test'},
	{name: 'View All', path: '/results/all'},
	{name: 'Extra', path: '/extra'},
	{name: 'Past Results', path: '/archive'},
	{name: 'Acknowledgements', path: '/acknowledgements'},
	{name: 'About', path: '/about'},
	{name: 'Feedback/Jury Suggestions', path: '/feedback'},
];

const ongoingRoutes = [
	{name: 'Past Results', path: '/archive'},
	{name: 'About', path: '/about'},
	{name: 'Feedback/Jury Suggestions', path: '/feedback'},
];

module.exports = {
	resultsRoutes,
	ongoingRoutes,
};
