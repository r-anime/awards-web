/* eslint-disable no-await-in-loop */
const apiApp = require('polka')();
const sequelize = require('../models').sequelize;

// Sequelize models to avoid redundancy
const Answers = sequelize.model('answers');
const Applicants = sequelize.model('applicants');
const Applications = sequelize.model('applications');
const QuestionGroups = sequelize.model('question_groups');
const Questions = sequelize.model('questions');
const Scores = sequelize.model('scores');
const Users = sequelize.model('users');
const Categories = sequelize.model('categories');
const Jurors = sequelize.model('jurors');

// eslint-disable-next-line no-unused-vars
const log = require('another-logger');
const {yuuko} = require('../bot/index');
const config = require('../config');
const Allocations = require('../util/allocations');

apiApp.get('/applications', async (request, response) => {
	try {
		response.json(await Applications.findAll({where: {active: true}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/applications/latest/full', async (request, response) => {
	try {
		let apps = await Applications.findAll({
			limit: 1,
			where: {active: true},
			order: [['year', 'DESC']],
			include: [
				{
					model: QuestionGroups,
					as: 'question_groups',
					include: [
						{
							model: Questions,
							as: 'questions',
						},
					],
				},
			],
		});
		apps = apps[0].get();
		for (let i = 0; i < apps.question_groups.length; i++) {
			apps.question_groups[i] = apps.question_groups[i].get();
			for (let j = 0; j < apps.question_groups[i].questions.length; j++) {
				apps.question_groups[i].questions[j] = apps.question_groups[i].questions[j].get();
			}
		}
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
				{
					model: Applications,
					as: 'application',
				},
			],
		});
		questionGroups = questionGroups.map(qg => {
			const data = qg.get();
			for (let i = 0; i < data.questions.length; i++) {
				const questionData = data.questions[i].get();
				data.questions[i] = questionData;
			}
			data.application = data.application.get();
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
				active: true,
			},
			raw: false,
			include: [
				{
					model: Questions,
					as: 'questions',
				},
				{
					model: Applications,
					as: 'application',
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
		await QuestionGroups.update({order: questionGroup.order, weight: questionGroup.weight, name: questionGroup.name}, {where: {id: request.params.id, active: true}});
		const ogQuestions = await Questions.findAll({where: {group_id: request.params.id, active: true}});
		const promiseArr = [];
		const t = await sequelize.transaction();
		for (const question of questionGroup.questions) {
			if (question.id) {
				const found = ogQuestions.find(quest => quest.id === question.id);
				if (found) {
					promiseArr.push(Questions.update(question, {
						where: {
							id: question.id,
							active: true,
						},
						transaction: t,
					}));
				} else {
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

apiApp.get('/all-answers', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to retrieve scores and answers.'});
	}
	try {
		response.json(await Answers.findAll({
			where: {
				active: true,
			},
			attributes: ['id', 'question_id', 'applicant_id'],
			include: [
				{
					model: Questions,
					as: 'question',
					include: [
						{
							model: QuestionGroups,
							as: 'question_group',
							include: [
								{
									model: Applications,
									as: 'application',
								},
							],
						},
					],
				},
				{
					model: Applicants,
					as: 'applicant',
					include: [
						{
							model: Users,
							as: 'user',
						},
					],
				},
				{
					model: Scores,
					as: 'scores',
				},
			],
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/answers/:applicantID', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to retrieve scores and answers.'});
	}
	try {
		response.json(await Answers.findAll({
			where: {
				active: true,
				applicant_id: request.params.applicantID,
			},
			include: [
				{
					model: Questions,
					as: 'question',
					include: [
						{
							model: QuestionGroups,
							as: 'question_group',
							include: [
								{
									model: Applications,
									as: 'application',
								},
							],
						},
					],
				},
				{
					model: Applicants,
					as: 'applicant',
					include: [
						{
							model: Users,
							as: 'user',
						},
					],
				},
				{
					model: Scores,
					as: 'scores',
				},
			],
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/random-answer/:questionID', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to retrieve scores and answers.'});
	}
	try {
		let answers = await Answers.findAll({
			where: {
				active: true,
				question_id: request.params.questionID,
			},
			include: [
				{
					model: Questions,
					as: 'question',
					include: [
						{
							model: QuestionGroups,
							as: 'question_group',
							include: [
								{
									model: Applications,
									as: 'application',
								},
							],
						},
					],
				},
				{
					model: Applicants,
					as: 'applicant',
					include: [
						{
							model: Users,
							as: 'user',
						},
					],
				},
				{
					model: Scores,
					as: 'scores',
				},
			],
		});
		answers = answers.filter(answer => answer.scores.length < 3 && !answer.scores.find(score => score.host_name === auth));
		if (answers.length) {
			response.json(answers[Math.floor(Math.random() * answers.length)]);
		} else {
			response.empty();
		}
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/grouped-answers', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to retrieve scores and answers.'});
	}
	try {
		const prefNumber = await Answers.findAll({
			where: {
				active: true,
			},
			attributes: [
				'applicant_id',
				sequelize.fn('count', sequelize.col('applicant_id')),
			],
			group: ['Answers.applicant_id'],
		});
		const answerNumber = await Answers.findAll({
			where: {
				active: true,
			},
			include: [{
				model: Questions,
				as: 'question',
				where: {
					type: 'essay',
				},
				attributes: ['id'],
			}],
			attributes: [
				'applicant_id',
				sequelize.fn('count', sequelize.col('applicant_id')),
			],
			group: ['Answers.applicant_id'],
		});
		response.json({
			prefNumber,
			answerNumber,
		});
	} catch (error) {
		response.error(error);
	}
});


apiApp.post('/score', async (request, response) => {
	const auth = await request.authenticate({level: 2, lock: 'grading-open'});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to grade apps. Alternatively, grading may not be open yet.'});
	}
	let score;
	try {
		score = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	try {
		score = await Scores.create(score);
		const answer = await Answers.findOne({
			where: {
				id: score.answer_id,
			},
			include: [
				{
					model: Questions,
					as: 'question',
				},
			],
		});
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'Application Answer Scored',
				description: `A Juror App answer was scored by **${auth}**. The question was **${answer.question.question}**.`,
				color: 8302335,
			},
		});
		response.json(score);
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/score/:id', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to delete scores.'});
	}
	try {
		await Scores.destroy({where: {id: request.params.id}});
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'Application Score Deleted',
				description: `An application score was deleted by **${auth}**.`,
				color: 8302335,
			},
		});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/applicants', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be host to retrieve applicants.'});
	}
	try {
		response.json(await Applicants.findAll({
			where: {
				active: true,
			},
			include: [
				{
					model: Users,
					as: 'user',
				},
			],
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/applicant/:id', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to delete an applicant.'});
	}
	try {
		await Applicants.update({active: false}, {where: {id: request.params.id}});
		await Answers.update(
			{
				active: false,
			},
			{
				where: {
					applicant_id: request.params.id,
				},
			},
		);
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'Applicant Deleted',
				description: `An applicant for jury was binned by **${auth}**.`,
				color: 8302335,
			},
		});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/applicant', async (request, response) => {
	let userName;
	if (!await request.authenticate({name: userName, lock: 'apps-open'})) {
		return response.json(401, {error: 'Invalid user.'});
	}
	if (request.session.reddit_name) {
		userName = request.session.reddit_name;
	} else {
		userName = (await request.reddit().get('/api/v1/me')).body.name;
	}
	try {
		let applicant = await Applicants.findAll({
			where: {
				'$user.reddit$': userName,
			},
			limit: 1,
			order: [['app_id', 'DESC']],
			include: [
				{
					model: Users,
					as: 'user',
				},
			],
		});
		if (applicant.length) {
			applicant = applicant[0].get();
			applicant.user = applicant.user.get();
			response.json(applicant);
		} else {
			response.json(401, {error: 'Applicant not registered. Please log out from the navbar and log back in.'});
		}
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/submit', async (request, response) => {
	let userName;
	if (request.session.reddit_name) {
		userName = request.session.reddit_name;
	} else {
		userName = (await request.reddit().get('/api/v1/me')).body.name;
	}
	if (!await request.authenticate({name: userName, lock: 'apps-open'}, {name: request.session.reddit_name})) {
		return response.json(401, {error: 'Invalid user.'});
	}
	let req;
	try {
		req = await request.json();
	} catch (error) {
		return response.json(400, {error: 'Invalid JSON'});
	}
	try {
		if (req.answer.length > 5000) {
			response.json(401, {error: 'You are over the character limit for answers.'});
			return;
		}
		// eslint-disable-next-line no-unused-vars
		const [answer, created] = await Answers.findOrCreate({
			where: {
				question_id: req.question_id,
				applicant_id: req.applicant_id,
			},
			defaults: {
				answer: req.answer,
			},
		});
		if (!created) {
			await Answers.update(
				{
					answer: req.answer,
				},
				{
					where: {
						question_id: req.question_id,
						applicant_id: req.applicant_id,
					},
				},
			);
		}
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/my-answers/:applicant_id', async (request, response) => {
	let userName;
	if (request.session.reddit_name) {
		userName = request.session.reddit_name;
	} else {
		userName = (await request.reddit().get('/api/v1/me')).body.name;
	}
	if (!await request.authenticate({name: userName})) {
		return response.json(401, {error: 'Invalid user.'});
	}
	try {
		response.json(await Answers.findAll({
			where: {
				applicant_id: request.params.applicant_id,
			},
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/clean', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to clean jury applications.'});
	}
	try {
		const answers = await Answers.findAll({
			where: {
				active: true,
			},
			include: [{
				model: Questions,
				as: 'question',
			}],
		});
		const applicants = await Applicants.findAll({
			where: {
				active: true,
			},
		});
		for (const applicant of applicants) {
			if (answers.filter(answer => answer.applicant_id === applicant.id && answer.question.type === 'essay').length === 0) {
				await Answers.destroy({
					where: {
						applicant_id: applicant.id,
					},
				});
				await Applicants.destroy({where: {
					id: applicant.id,
				}});
			}
		}
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/allocations', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to roll jurors.'});
	}
	Promise.all([
		Categories.findAll({where: {active: 1}}),
		Answers.findAll({
			where: {
				active: true,
			},
			include: [
				{
					model: Questions,
					as: 'question',
					include: [
						{
							model: QuestionGroups,
							as: 'question_group',
							include: [
								{
									model: Applications,
									as: 'application',
								},
							],
						},
					],
				},
				{
					model: Applicants,
					as: 'applicant',
					include: [
						{
							model: Users,
							as: 'user',
						},
					],
				},
				{
					model: Scores,
					as: 'scores',
				},
			],
		}),
		Questions.findAll({
			where: {
				active: true,
			},
			include: [{
				model: QuestionGroups,
				as: 'question_group',
				include: [{
					model: Applications,
					as: 'application',
				}],
			}],
		}),
	]).then(async promiseArr => {
		try {
			// Change this every year
			const allocationInstance = new Allocations(promiseArr[0], promiseArr[1].filter(answer => answer.question.question_group.application.year === 2020), promiseArr[2].filter(question => question.question_group.application.year === 2020 && question.type === 'essay'));
			allocationInstance.initiateDraft();
			const jurorPromiseArr = [];
			await Jurors.destroy({truncate: true, restartIdentity: true});
			await sequelize.query("DELETE FROM sqlite_sequence WHERE name = 'jurors'");
			const t = await sequelize.transaction();
			for (const juror of allocationInstance.allocatedJurors) {
				jurorPromiseArr.push(Jurors.create({
					name: juror.name,
					link: `https://www.reddit.com/user/${juror.name}`,
					score: juror.score,
					preference: juror.preference,
					categoryId: juror.categoryId,
				}, {
					transaction: t,
				}));
			}
			Promise.all(jurorPromiseArr).then(async () => {
				await t.commit();
				yuuko.createMessage(config.discord.auditChannel, {
					embed: {
						title: 'Jurors rolled',
						description: `Jurors were rolled by **${auth}**.`,
						color: 8302335,
					},
				});
				response.json(allocationInstance.allocatedJurors);
			});
		} catch (error) {
			response.error(error);
		}
	});
});

module.exports = apiApp;
