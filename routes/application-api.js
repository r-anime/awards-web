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

apiApp.get('/applications/latest/full', async (request, response) => {
	try {
		const apps = await Applications.findAll({
			limit: 1,
			where: {active: true},
			order: [['year', 'DESC']],
			include: {
				all: true,
				nested: true,
				// model: QuestionGroups,
				// as: 'question_groups',
			},
		});
		response.json(apps);
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
		let questionGroups = await QuestionGroups.findAll({
			where: {
				active: true,
			},
			raw: false,
			include: [
				{
					model: Questions,
					as: 'questions',
				},
			],
		});
		questionGroups = questionGroups.map(qg => {
			const data = qg.get();
			for (let i = 0; i < data.questions.length; i++) {
				const questionData = data.questions[i].get();
				data.questions[i] = questionData;
			}
			return data;
		});
		response.json(questionGroups);
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/question-group', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to create a question group'});
	}
	let questionGroup;
	try {
		questionGroup = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	try {
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'New Question Group',
				description: `A new Juror App Question Group was created by **${auth}**.`,
				color: 8302335,
			},
		});
		questionGroup = await QuestionGroups.create({
			name: questionGroup.name,
			order: questionGroup.order,
			weight: questionGroup.weight,
			app_id: questionGroup.app_id,
		});
		questionGroup = await QuestionGroups.findOne({
			where: {
				id: questionGroup.id,
			},
			raw: false,
			include: [
				{
					model: Questions,
					as: 'questions',
				},
			],
		});
		response.json(questionGroup.get());
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/question-group/:id', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to delete a question group'});
	}
	try {
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'Question Group Deleted',
				description: `A Juror App Question Group was deleted by **${auth}**.`,
				color: 8302335,
			},
		});
		await QuestionGroups.update({active: false}, {where: {id: request.params.id}});
		await Questions.update({active: false}, {where: {group_id: request.params.id}});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.patch('/question-group/:id', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to modify a question group'});
	}
	let questionGroup;
	try {
		questionGroup = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	try {
		await QuestionGroups.update({order: questionGroup.order, weight: questionGroup.weight, name: questionGroup.name}, {where: {id: request.params.id}});
		const ogQuestions = await Questions.findAll({where: {group_id: request.params.id}});
		const promiseArr = [];
		const t = await sequelize.transaction();
		for (const question of questionGroup.questions) {
			if (question.id) {
				const found = ogQuestions.find(quest => quest.id === question.id);
				if (!found) {
					promiseArr.push(Questions.create(question, {transaction: t}));
				}
			} else {
				promiseArr.push(Questions.create(question, {transaction: t}));
			}
		}
		for (const question of ogQuestions) {
			const found = questionGroup.questions.find(quest => {
				if (!quest.id) return false;
				return quest.id === question.id;
			});
			if (!found) {
				promiseArr.push(Questions.destroy({where: {id: question.id}, transaction: t}));
			}
		}
		Promise.all(promiseArr).then(async () => {
			await t.commit();
			yuuko.createMessage(config.discord.auditChannel, {
				embed: {
					title: 'Application Questions Modified',
					description: `A Juror App Question Group was modified by **${auth}**.`,
					color: 8302335,
				},
			});
			response.empty();
		});
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
