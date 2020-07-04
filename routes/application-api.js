/* eslint-disable no-await-in-loop */
const apiApp = require('polka')();
const sequelize = require('../models').sequelize;

// Sequelize models to avoid redundancy
const Answers = sequelize.model('answers');
const Applicants = sequelize.model('applicants');
const Applications = sequelize.model('applications');
const QuestionAnswers = sequelize.model('question_answers');
const QuestionGroups = sequelize.model('question_groups');
const Questions = sequelize.model('questions');
const Scores = sequelize.model('scores');

// eslint-disable-next-line no-unused-vars
const log = require('another-logger');
const {yuuko} = require('../bot/index');
const config = require('../config');

apiApp.get('/applications', async (request, response) => {
	try {
		response.json(await Applications.findAll({where: {active: true}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/application', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to create a new application.'});
	}
	let application;
	try {
		application = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}

	try {
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'Juror App Created',
				description: `A new Juror App for the year **${application.year}** was created by **${auth}**.`,
				color: 8302335,
			},
		});
		response.json(await Applications.create(application));
	} catch (error) {
		response.error(error);
	}
});

apiApp.patch('/application', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to modify juror apps.'});
	}
	let application;
	try {
		application = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	let action;
	if (application.active) action = 'modified';
	else action = 'deleted';
	try {
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: `Juror App ${action}`,
				description: `A Juror App for the year **${application.year}** was ${action} by **${auth}**.`,
				color: 8302335,
			},
		});
		await Applications.update(application, {where: {id: application.id}});
		response.json(await Applications.findOne({where: {active: true, id: application.id}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/question-groups', async (request, response) => {
	try {
		response.json(await QuestionGroups.findAll({
			where: {
				active: true,
			},
			include: [{
				model: Applications,
				as: 'application',
				required: true,
			},
			{
				model: Questions,
				required: true,
			}],
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/answers', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to retrieve scores and answers.'});
	}
	try {
		response.json(await Answers.findAll({
			where: {
				active: true,
			},
			include: [{
				model: Questions,
				as: 'question',
				required: true,
			},
			{
				model: Applicants,
				as: 'applicant',
				required: true,
			}],
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/scores', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to retrieve scores and answers.'});
	}
	try {
		response.json(await Scores.findAll({
			include: {
				model: Answers,
				as: 'answer',
				required: true,
			},
		}));
	} catch (error) {
		response.error(error);
	}
});

module.exports = apiApp;
