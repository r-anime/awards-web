const apiApp = require('polka')();
const {yuuko} = require('../bot/index');
const config = require('../config');

apiApp.post('/allocations', async (request, response) => {
	let req;
	try {
		req = await request.json();
	} catch (error) {
		response.error(error);
	}
	try {
		yuuko.createMessage(config.discord.complaintsChannel, {
			embed: {
				title: 'New Complaint',
				description: req,
				color: 8302335,
			},
		});
	} catch (error) {
		response.error(error);
	}
	response.empty();
});

module.exports = apiApp;
