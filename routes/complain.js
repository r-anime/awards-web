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
	if (request.session.complaints) {
		request.session.complaints++;
	} else {
		request.session.complaints = 1;
	}
	if (request.session.complaints < 5) {
		try {
			yuuko.createMessage(config.discord.complaintsChannel, {
				embed: {
					title: 'New Allocation Complaint',
					description: req,
					color: 8302335,
				},
			});
		} catch (error) {
			response.error(error);
		}
		response.empty();
	} else {
		response.json(401, {error: 'You are making too many complaints.'});
	}
});

apiApp.post('/feedback', async (request, response) => {
	let req;
	try {
		req = await request.json();
	} catch (error) {
		response.error(error);
	}
	if (request.session.complaints) {
		request.session.complaints++;
	} else {
		request.session.complaints = 1;
	}
	if (request.session.complaints <= 5) {
		try {
			yuuko.createMessage(config.discord.feedbackChannel, {
				embed: {
					title: `Feedback from ${req.user ? req.user : 'Anonymous'}`,
					description: req.message,
					color: 8302335,
				},
			});
		} catch (error) {
			response.error(error);
		}
		response.empty();
	} else {
		response.json(401, {error: 'You are making too many complaints.'});
	}
});

module.exports = apiApp;
