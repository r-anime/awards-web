/* eslint-disable camelcase */
const apiApp = require('polka')();
const {yuuko} = require('../bot/index');
const config = require('../config');
const jwt = require('jsonwebtoken');

const sequelize = require('../models').sequelize;
const Feedback = sequelize.model('feedback');

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
	if (request.session.complaints < 10) {
		try {
			const ip_hash = jwt.sign(request.clientIp, config.private_key);
			await Feedback.create({
				feedback: req,
				ip_hash,
			});
			yuuko.createMessage(config.discord.complaintsChannel, {
				embed: {
					title: 'New Allocation Complaint',
					description: req,
					color: 8302335,
				},
			}).then(() => {
				response.empty();
			}, () => {
				response.json(500, {error: 'Something went wrong submitting your feedback.'});
			});
		} catch (error) {
			response.error(error);
		}
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
	if (request.session.complaints <= 10) {
		try {
			const ip_hash = jwt.sign(req.ip, config.private_key);
			await Feedback.create({
				feedback: req.message,
				reddit: req.user ? req.user : null,
				ip_hash,
			});
			yuuko.createMessage(config.discord.feedbackChannel, {
				embed: {
					title: `Feedback from ${req.user ? req.user : 'Anonymous'} (${req.ip})`,
					description: req.message,
					color: 8302335,
				},
			}).then(() => {
				response.empty();
			}, () => {
				response.json(500, {error: 'Something went wrong submitting your feedback.'});
			});
		} catch (error) {
			response.error(error);
		}
	} else {
		response.json(401, {error: 'You are making too many complaints.'});
	}
});

apiApp.post('/suggest', async (request, response) => {
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
	if (request.session.complaints <= 10) {
		try {
			const ip_hash = jwt.sign(request.clientIp, config.private_key);
			await Feedback.create({
				feedback: req.suggestion,
				ip_hash,
			});
			yuuko.createMessage(config.discord.feedbackChannel, {
				embed: {
					title: `Suggestion for ${req.category}`,
					description: req.suggestion,
					color: 8302335,
				},
			}).then(() => {
				response.empty();
			}, () => {
				response.json(500, {error: 'Something went wrong submitting your feedback.'});
			});
		} catch (error) {
			response.error(error);
		}
	} else {
		response.json(401, {error: 'You are making too many complaints.'});
	}
});

module.exports = apiApp;
